<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLogged()) {
    header("Location: login.php");
    exit;
}

$idUser = $_SESSION['idUser'];
$errores = [];
$exito = '';

// Mensajes GET
if (isset($_GET['creada'])) $exito = "Cita creada correctamente.";
if (isset($_GET['borrada'])) $exito = "Cita eliminada.";
if (isset($_GET['editada'])) $exito = "Cita actualizada correctamente.";
if (isset($_GET['error'])) $errores[] = $_GET['error'];

// Crear cita
if (isset($_POST['crear_cita'])) {

    $fecha = $_POST['fecha_cita'] ?? '';
    $motivo = trim($_POST['motivo_cita'] ?? '');

    if (!empty($fecha) && $fecha < date('Y-m-d')) {
        header("Location: citaciones.php?error=" . urlencode("La fecha debe ser igual o posterior a hoy."));
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO citas (idUser, fecha_cita, motivo_cita)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$idUser, $fecha, $motivo]);

    header("Location: citaciones.php?creada=1");
    exit;
}

// Borrar cita
if (isset($_GET['borrar'])) {

    $idCita = $_GET['borrar'];

    $stmt = $pdo->prepare("
        SELECT fecha_cita FROM citas WHERE idCita=? AND idUser=?
    ");
    $stmt->execute([$idCita, $idUser]);
    $cita = $stmt->fetch();

    if ($cita && $cita['fecha_cita'] >= date('Y-m-d')) {
        $pdo->prepare("DELETE FROM citas WHERE idCita=?")->execute([$idCita]);
        header("Location: citaciones.php?borrada=1");
        exit;
    } else {
        header("Location: citaciones.php?error=" . urlencode("No puedes borrar citas pasadas."));
        exit;
    }
}

// Editar cita
if (isset($_POST['editar_cita'])) {

    $idCita = $_POST['idCita'];
    $fecha = $_POST['fecha_cita'] ?? '';
    $motivo = trim($_POST['motivo_cita'] ?? '');

    $stmt = $pdo->prepare("
        SELECT fecha_cita FROM citas WHERE idCita=? AND idUser=?
    ");
    $stmt->execute([$idCita, $idUser]);
    $cita = $stmt->fetch();

    if (!$cita || $cita['fecha_cita'] < date('Y-m-d')) {
        header("Location: citaciones.php?error=" . urlencode("No puedes modificar citas pasadas."));
        exit;
    }

    if (!empty($fecha) && $fecha < date('Y-m-d')) {
        header("Location: citaciones.php?error=" . urlencode("No puedes mover la cita a una fecha pasada."));
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE citas
        SET fecha_cita=?, motivo_cita=?
        WHERE idCita=? AND idUser=?
    ");
    $stmt->execute([$fecha, $motivo, $idCita, $idUser]);

    header("Location: citaciones.php?editada=1");
    exit;
}

// Obtener citas del usuario
$stmt = $pdo->prepare("
    SELECT * FROM citas
    WHERE idUser=?
    ORDER BY fecha_cita
");
$stmt->execute([$idUser]);
$citas = $stmt->fetchAll();
?>

<?php 
$pageTitle = "Citaciones";
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

    <!-- TÍTULO PRINCIPAL DE LA PÁGINA -->
    <h1 class="public-title">Gestión de citas</h1>

    <!-- MENSAJES -->
    <?php if (!empty($errores)): ?>
        <div class="mensaje-error">
            <?php foreach ($errores as $e): ?>
                <?= $e ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
        <div class="mensaje-exito">
            <?= $exito ?>
        </div>
    <?php endif; ?>

<!-- SECCIÓN: CREAR NUEVA CITA -->
    <section class="public-section">
        <div class="public-container">      

        <h2 class="public-subtitle">Crear nueva cita</h2>

        <form method="POST" class="form">
            <input type="hidden" name="crear_cita" value="1">

            <label>Fecha:</label>
            <input type="date" name="fecha_cita" required>

            <label>Especialidad (motivo):</label>
            <select name="motivo_cita" class="select-cita" required>
                <option value="">Seleccione una especialidad</option>
                <option value="Medicina General">Medicina General</option>
                <option value="Cardiología">Cardiología</option>
                <option value="Radiología Digital">Radiología Digital</option>
                <option value="Resonancia Magnética 3D">Resonancia Magnética 3D</option>
                <option value="Análisis clínicos">Análisis clínicos</option>
                <option value="Pediatría">Pediatría</option>
                <option value="Fisioterápia">Fisioterápia</option>
                <option value="Oftalmología">Oftalmología</option>
                <option value="Podología">Podología</option>
                <option value="Odontología">Odontología</option>
            </select>

            <button type="submit" class="btn btn-primario">Crear cita</button>
        </form>

        <hr>

        <!-- SECCIÓN: MIS CITAS -->
        <h2 class="public-subtitle">Mis citas</h2>

        <!-- PLACEHOLDER CUANDO NO HAY CITAS -->
        <?php if (empty($citas)): ?>
            <div class="citas-placeholder">
                <p>Aún no tienes citas asignadas.</p>
            </div>
        <?php endif; ?>


        <!-- LISTADO DE CITAS -->
            <?php foreach ($citas as $c): ?>

                <?php $esPasada = ($c['fecha_cita'] < date('Y-m-d')); ?>

                <div class="card cita-card <?= $esPasada ? 'cita-pasada' : '' ?>">

                    <?php if ($esPasada): ?>
                        <div class="badge-pasada">
                            <span class="check-icon">✔</span> Cita pasada
                        </div>
                    <?php endif; ?>

                    <p><strong>Fecha:</strong> <?= date("d/m/Y", strtotime($c['fecha_cita'])) ?></p>
                    <p><strong>Motivo:</strong> <?= $c['motivo_cita'] ?></p>

                    <?php if (!$esPasada): ?>

                        <form method="POST" class="form form-cita-editar">
                            <input type="hidden" name="editar_cita" value="1">
                            <input type="hidden" name="idCita" value="<?= $c['idCita'] ?>">

                            <label>Nueva fecha:</label>
                            <input type="date" name="fecha_cita" value="<?= $c['fecha_cita'] ?>" required>

                            <label>Especialidad (motivo):</label>
                            <select name="motivo_cita" class="select-cita" required>
                                <option value="">Seleccione una especialidad</option>

                                <option value="Medicina General" <?= $c['motivo_cita'] == 'Medicina General' ? 'selected' : '' ?>>Medicina General</option>
                                <option value="Cardiología" <?= $c['motivo_cita'] == 'Cardiología' ? 'selected' : '' ?>>Cardiología</option>
                                <option value="Radiología Digital" <?= $c['motivo_cita'] == 'Radiología Digital' ? 'selected' : '' ?>>Radiología Digital</option>
                                <option value="Resonancia Magnética 3D" <?= $c['motivo_cita'] == 'Resonancia Magnética 3D' ? 'selected' : '' ?>>Resonancia Magnética 3D</option>
                                <option value="Análisis clínicos" <?= $c['motivo_cita'] == 'Análisis clínicos' ? 'selected' : '' ?>>Análisis clínicos</option>
                                <option value="Pediatría" <?= $c['motivo_cita'] == 'Pediatría' ? 'selected' : '' ?>>Pediatría</option>
                                <option value="Fisioterápia" <?= $c['motivo_cita'] == 'Fisioterápia' ? 'selected' : '' ?>>Fisioterápia</option>
                                <option value="Oftalmología" <?= $c['motivo_cita'] == 'Oftalmología' ? 'selected' : '' ?>>Oftalmología</option>
                                <option value="Podología" <?= $c['motivo_cita'] == 'Podología' ? 'selected' : '' ?>>Podología</option>
                                <option value="Odontología" <?= $c['motivo_cita'] == 'Odontología' ? 'selected' : '' ?>>Odontología</option>
                            </select>

                            <button type="submit" class="btn btn-primario">Guardar cambios</button>
                        </form>

                        <a href="citaciones.php?borrar=<?= $c['idCita'] ?>"
                            class="btn btn-peligro"
                            onclick="return confirm('¿Seguro que quieres borrar esta cita?')">
                            Borrar
                        </a>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>
        </div>             
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="scripts/mensajes.js"></script>

</body>
</html>