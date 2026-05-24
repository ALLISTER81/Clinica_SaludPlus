<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// ------------------------------------------------------
// 1. Verificar acceso
// ------------------------------------------------------
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// ------------------------------------------------------
// 2. Obtener lista de usuarios
// ------------------------------------------------------
$usuarios = $pdo->query("
    SELECT idUser, nombre, apellidos 
    FROM users_data 
    ORDER BY nombre
")->fetchAll();

$exito = $_GET['exito'] ?? '';
$errores = [];
if (isset($_GET['error'])) $errores[] = $_GET['error'];

// ------------------------------------------------------
// 3. Crear cita desde admin
// ------------------------------------------------------
if (isset($_POST['crear_cita_admin'])) {

    $idUser = $_POST['idUser'];
    $fecha = $_POST['fecha_cita'];
    $motivo = trim($_POST['motivo_cita']);

    if ($fecha < date('Y-m-d')) {
        header("Location: citas-administracion.php?idUser=$idUser&error=" . urlencode("La fecha debe ser igual o posterior a hoy."));
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO citas (idUser, fecha_cita, motivo_cita)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$idUser, $fecha, $motivo]);

    header("Location: citas-administracion.php?idUser=$idUser&exito=" . urlencode("Cita creada correctamente."));
    exit;
}

// ------------------------------------------------------
// 4. Borrar cita
// ------------------------------------------------------
if (isset($_GET['borrar'])) {

    $idCita = $_GET['borrar'];

    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$idCita]);
    $cita = $stmt->fetch();

    if ($cita) {
        $pdo->prepare("DELETE FROM citas WHERE idCita=?")->execute([$idCita]);
        header("Location: citas-administracion.php?idUser={$cita['idUser']}&exito=Cita borrada correctamente");
        exit;
    }
}

// ------------------------------------------------------
// 5. Guardar cambios de edición
// ------------------------------------------------------
if (isset($_POST['guardar_cambios'])) {

    $idCita = $_POST['idCita'];
    $fecha = date('Y-m-d', strtotime($_POST['fecha_cita']));
    $motivo = trim($_POST['motivo_cita']);
    $hoy = date('Y-m-d');

    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$idCita]);
    $cita = $stmt->fetch();

    if ($fecha < $hoy) {
        $mensaje = urlencode("No puedes mover la cita a una fecha pasada.");
        header("Location: citas-administracion.php?editar=$idCita&idUser={$cita['idUser']}&error=$mensaje");
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE citas
        SET fecha_cita=?, motivo_cita=?
        WHERE idCita=?
    ");
    $stmt->execute([$fecha, $motivo, $idCita]);

    header("Location: citas-administracion.php?idUser={$cita['idUser']}&exito=Cita actualizada correctamente");
    exit;
}

// ------------------------------------------------------
// 6. Determinar idUser seleccionado
// ------------------------------------------------------
$idUserSeleccionado = null;

if (isset($_GET['editar'])) {

    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$_GET['editar']]);
    $idUserSeleccionado = $stmt->fetchColumn();

} elseif (isset($_GET['borrar'])) {

    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$_GET['borrar']]);
    $idUserSeleccionado = $stmt->fetchColumn();

} elseif (isset($_GET['idUser'])) {

    $idUserSeleccionado = $_GET['idUser'];
}

// ------------------------------------------------------
// 7. Cargar cita para edición
// ------------------------------------------------------
$citaEditar = null;

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idCita=?");
    $stmt->execute([$_GET['editar']]);
    $citaEditar = $stmt->fetch();
}

// ------------------------------------------------------
// 8. Obtener citas del usuario
// ------------------------------------------------------
$citas = [];

if ($idUserSeleccionado) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.nombre, u.apellidos
        FROM citas c
        JOIN users_data u ON c.idUser = u.idUser
        WHERE c.idUser = ?
        ORDER BY c.fecha_cita ASC
    ");
    $stmt->execute([$idUserSeleccionado]);
    $citas = $stmt->fetchAll();
}

// ------------------------------------------------------
// 9. Especialidades (para creación y edición)
// ------------------------------------------------------
$especialidades = [
    "Medicina General", "Cardiología", "Radiología Digital",
    "Resonancia Magnética 3D", "Análisis clínicos", "Pediatría",
    "Fisioterápia", "Oftalmología", "Podología", "Odontología"
];
?>

<?php 
$pageTitle = "Administración de citas";
$isAdmin = true;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<main>

    <!-- TÍTULO PRINCIPAL -->
    <section class="page-title">
        <h1 class="admin-title">Administración de citas</h1>
    </section>

    <!-- MENSAJES -->
    <?php if (!empty($errores)): ?>
        <div class="mensaje-error">
            <?php foreach ($errores as $e): ?>
                <?= $e ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
        <div class="mensaje-exito"><?= $exito ?></div>
    <?php endif; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="admin-section">
        <div class="admin-container">

            <!-- Selección de usuario -->
            <form method="GET" class="admin-form">
                <label>Seleccionar paciente:</label>

                <select name="idUser" required>
                    <option value="">Seleccione un usuario</option>

                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['idUser'] ?>"
                            <?= ($idUserSeleccionado == $u['idUser']) ? 'selected' : '' ?>>
                            <?= $u['nombre'] . " " . $u['apellidos'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-primario">Ver citas</button>
            </form>

            <!-- FORMULARIO DE CREACIÓN -->
            <?php if ($idUserSeleccionado && !$citaEditar): ?>
                <section class="admin-section">

                    <h2 class="admin-subtitle">Crear nueva cita para este usuario</h2>

                    <form method="POST" class="admin-form-edit">

                        <input type="hidden" name="crear_cita_admin" value="1">
                        <input type="hidden" name="idUser" value="<?= $idUserSeleccionado ?>">

                        <label>Fecha:</label>
                        <input type="date" name="fecha_cita" required>

                        <label>Especialidad:</label>
                        <select name="motivo_cita" required>
                            <option value="">Seleccione una especialidad</option>

                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= $esp ?>"><?= $esp ?></option>
                            <?php endforeach; ?>
                        </select>

                        <button class="btn btn-primario">Crear cita</button>

                    </form>

                </section>
            <?php endif; ?>

            <!-- FORMULARIO DE EDICIÓN -->
            <?php if ($citaEditar): ?>
                <section class="admin-section">

                    <h2 class="admin-subtitle">Editando cita</h2>

                    <form method="POST" class="admin-form-edit">

                        <input type="hidden" name="guardar_cambios" value="1">
                        <input type="hidden" name="idCita" value="<?= $citaEditar['idCita'] ?>">

                        <label>Nueva fecha:</label>
                        <input type="date" name="fecha_cita" value="<?= $citaEditar['fecha_cita'] ?>" required>

                        <label>Especialidad:</label>
                        <select name="motivo_cita" required>
                            <option value="">Seleccione una especialidad</option>

                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= $esp ?>" <?= ($citaEditar['motivo_cita'] == $esp) ? 'selected' : '' ?>>
                                    <?= $esp ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button class="btn btn-primario">Guardar cambios</button>

                    </form>

                </section>
            <?php endif; ?>

        </div>
    </section>

    <!-- TABLA DE CITAS -->
    <?php if ($idUserSeleccionado): ?>

        <section class="admin-section">

            <?php if (empty($citas)): ?>

                <!-- PLACEHOLDER CUANDO NO HAY CITAS -->
                <div class="citas-placeholder">
                    <p>Este usuario no tiene citas registradas.</p>
                </div>

            <?php else: ?>

                <h2 class="admin-subtitle">
                    Citas de <?= $citas[0]['nombre'] . " " . $citas[0]['apellidos'] ?>
                </h2>

                <div class="admin-table-wrapper">
                    <table class="admin-table">

                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Motivo</th>
                            <th class="acciones">Acciones</th>
                        </tr>

                        <?php foreach ($citas as $c): ?>
                            <tr>
                                <td><?= $c['idCita'] ?></td>
                                <td><?= date("d/m/Y", strtotime($c['fecha_cita'])) ?></td>
                                <td><?= $c['motivo_cita'] ?></td>

                                <td class="acciones">

                                    <form method="GET" action="citas-administracion.php" class="form-inline">
                                        <input type="hidden" name="editar" value="<?= $c['idCita'] ?>">
                                        <input type="hidden" name="idUser" value="<?= $idUserSeleccionado ?>">
                                        <button class="btn-accion btn-editar">Editar</button>
                                    </form>

                                    <form method="GET" action="citas-administracion.php" class="form-inline">
                                        <input type="hidden" name="borrar" value="<?= $c['idCita'] ?>">
                                        <input type="hidden" name="idUser" value="<?= $idUserSeleccionado ?>">
                                        <button class="btn-accion btn-borrar"
                                                onclick="return confirm('¿Seguro que quieres borrar esta cita?')">
                                            Borrar
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </table>
                </div>

            <?php endif; ?>

        </section>

    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>

<?php if ($citaEditar): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.querySelector(".admin-form-edit");
        if (form) {
            form.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    });
</script>
<?php endif; ?>

</body>
</html>
