<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Verificar acceso
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// Obtener lista de usuarios
$usuarios = $pdo->query("
    SELECT idUser, nombre, apellidos 
    FROM users_data 
    ORDER BY nombre
")->fetchAll();

$exito = $_GET['exito'] ?? '';
$errores = [];
if (isset($_GET['error'])) $errores[] = $_GET['error'];


// ------------------------------------------------------
// BORRAR CITA
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
// GUARDAR CAMBIOS DE EDICIÓN
// ------------------------------------------------------
if (isset($_POST['guardar_cambios'])) {

    $idCita = $_POST['idCita'];
    $fecha = date('Y-m-d', strtotime($_POST['fecha_cita']));
    $motivo = trim($_POST['motivo_cita']);
    $hoy = date('Y-m-d');

    // Obtener idUser de la cita
    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$idCita]);
    $cita = $stmt->fetch();

    // Validación
    if ($fecha < $hoy) {
        $mensaje = urlencode("No puedes mover la cita a una fecha pasada.");
        header("Location: citas-administracion.php?editar=$idCita&idUser={$cita['idUser']}&error=$mensaje");
        exit;
    }

    // Actualizar
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
// DETERMINAR idUser SELECCIONADO
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
// CARGAR CITA PARA EDICIÓN
// ------------------------------------------------------
$citaEditar = null;

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idCita=?");
    $stmt->execute([$_GET['editar']]);
    $citaEditar = $stmt->fetch();
}


// ------------------------------------------------------
// OBTENER CITAS DEL USUARIO
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
<body>

<?php include 'includes/navbar.php'; ?>

<main>

    <!-- TÍTULO PRINCIPAL -->
    <section class="page-title">
        <h1 class="admin-title">Administración de citas</h1>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="admin-section">
        <div class="admin-container">

            <!-- Mensajes -->
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

            <!-- FORMULARIO DE EDICIÓN -->
            <?php if ($citaEditar): ?>
                <section class="admin-section" style="margin-top: 30px;">

                    <h2>Editando cita</h2>

                    <form method="POST" class="admin-form-edit">

                        <input type="hidden" name="guardar_cambios" value="1">
                        <input type="hidden" name="idCita" value="<?= $citaEditar['idCita'] ?>">

                        <label>Nueva fecha:</label>
                        <input type="date" name="fecha_cita" value="<?= $citaEditar['fecha_cita'] ?>" required>

                        <label>Especialidad:</label>
                        <select name="motivo_cita" required>
                            <option value="">Seleccione una especialidad</option>

                            <?php
                            $especialidades = [
                                "Medicina General", "Cardiología", "Radiología Digital",
                                "Resonancia Magnética 3D", "Análisis clínicos", "Pediatría",
                                "Fisioterápia", "Oftalmología", "Podología", "Odontología"
                            ];

                            foreach ($especialidades as $esp):
                            ?>
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

                <h2>Este usuario no tiene citas registradas</h2>

            <?php else: ?>

                <h2>
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

                                    <form method="GET" action="citas-administracion.php" style="display:inline;">
                                        <input type="hidden" name="editar" value="<?= $c['idCita'] ?>">
                                        <input type="hidden" name="idUser" value="<?= $idUserSeleccionado ?>">
                                        <button class="btn-accion btn-editar">Editar</button>
                                    </form>

                                    <form method="GET" action="citas-administracion.php" style="display:inline;">
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


