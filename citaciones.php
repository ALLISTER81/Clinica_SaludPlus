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

// Crear nueva cita
if (isset($_POST['crear_cita'])) {

    $fecha = $_POST['fecha_cita'];
    $motivo = trim($_POST['motivo_cita']);

    // Validar fecha futura
    if ($fecha < date('Y-m-d')) {
        $errores[] = "La fecha debe ser igual o posterior a hoy.";
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("
            INSERT INTO citas (idUser, fecha_cita, motivo_cita)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$idUser, $fecha, $motivo]);

        $exito = "Cita creada correctamente.";
    }
}

// Borrar cita (solo futuras)
if (isset($_GET['borrar'])) {

    $idCita = $_GET['borrar'];

    $stmt = $pdo->prepare("
        SELECT fecha_cita FROM citas WHERE idCita=? AND idUser=?
    ");
    $stmt->execute([$idCita, $idUser]);
    $cita = $stmt->fetch();

    if ($cita && $cita['fecha_cita'] >= date('Y-m-d')) {
        $pdo->prepare("DELETE FROM citas WHERE idCita=?")->execute([$idCita]);
        $exito = "Cita eliminada.";
    } else {
        $errores[] = "No puedes borrar citas pasadas.";
    }
}

// Editar cita (solo futuras)
if (isset($_POST['editar_cita'])) {

    $idCita = $_POST['idCita'];
    $fecha = $_POST['fecha_cita'];
    $motivo = trim($_POST['motivo_cita']);

    $stmt = $pdo->prepare("
        SELECT fecha_cita FROM citas WHERE idCita=? AND idUser=?
    ");
    $stmt->execute([$idCita, $idUser]);
    $cita = $stmt->fetch();

    if (!$cita || $cita['fecha_cita'] < date('Y-m-d')) {
        $errores[] = "No puedes modificar citas pasadas.";
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("
            UPDATE citas
            SET fecha_cita=?, motivo_cita=?
            WHERE idCita=? AND idUser=?
        ");
        $stmt->execute([$fecha, $motivo, $idCita, $idUser]);

        $exito = "Cita actualizada correctamente.";
    }
}

$stmt = $pdo->prepare("
    SELECT * FROM citas
    WHERE idUser=?
    ORDER BY fecha_cita
");
$stmt->execute([$idUser]);
$citas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <title>Mis citas</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Mis citas</h1>

<?php if (!empty($errores)): ?>
    <div style="color:red;">
        <?php foreach ($errores as $e): ?>
            <p><?= $e ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($exito): ?>
    <div style="color:green;">
        <p><?= $exito ?></p>
    </div>
<?php endif; ?>

<h2>Crear nueva cita</h2>

<form method="POST">
    <input type="hidden" name="crear_cita" value="1">

    <label>Fecha:</label>
    <input type="date" name="fecha_cita" required><br>

    <label>Motivo:</label>
    <textarea name="motivo_cita" required></textarea><br>

    <button type="submit">Crear cita</button>
</form>

<hr>

<h2>Mis citas</h2>

<?php foreach ($citas as $c): ?>
    <div>
        <p><strong>Fecha:</strong> <?= $c['fecha_cita'] ?></p>
        <p><strong>Motivo:</strong> <?= $c['motivo_cita'] ?></p>

        <?php if ($c['fecha_cita'] >= date('Y-m-d')): ?>
            <!-- Editar -->
            <form method="POST" style="margin-top:10px;">
                <input type="hidden" name="editar_cita" value="1">
                <input type="hidden" name="idCita" value="<?= $c['idCita'] ?>">

                <label>Nueva fecha:</label>
                <input type="date" name="fecha_cita" value="<?= $c['fecha_cita'] ?>" required>

                <label>Motivo:</label>
                <input type="text" name="motivo_cita" value="<?= $c['motivo_cita'] ?>" required>

                <button type="submit">Guardar cambios</button>
            </form>

            <!-- Borrar -->
            <a href="citaciones.php?borrar=<?= $c['idCita'] ?>" onclick="return confirm('¿Seguro que quieres borrar esta cita?')">Borrar</a>
        <?php endif; ?>

        <hr>
    </div>
<?php endforeach; ?>

</body>
</html>


