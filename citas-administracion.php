<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require 'conexion.php';

// Verificar acceso
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// Obtener lista de usuarios
$usuarios = $pdo->query("SELECT idUser, nombre, apellidos FROM users_data ORDER BY nombre")->fetchAll();

$errores = [];
$exito = "";

// ------------------------------------------------------
// BORRAR CITA
// ------------------------------------------------------
if (isset($_GET['borrar'])) {
    $idCita = $_GET['borrar'];

    // Obtener idUser antes de borrar
    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$idCita]);
    $cita = $stmt->fetch();

    if ($cita) {
        $pdo->prepare("DELETE FROM citas WHERE idCita=?")->execute([$idCita]);
        $exito = "Cita eliminada correctamente.";
        header("Location: citas-administracion.php?idUser=" . $cita['idUser']);
        exit;
    }
}

// ------------------------------------------------------
// CARGAR CITA PARA EDICIÓN
// ------------------------------------------------------
$citaEditar = null;

if (isset($_GET['editar'])) {
    $idEditar = $_GET['editar'];

    $stmt = $pdo->prepare("SELECT * FROM citas WHERE idCita=?");
    $stmt->execute([$idEditar]);
    $citaEditar = $stmt->fetch();
}

// ------------------------------------------------------
// GUARDAR CAMBIOS DE EDICIÓN
// ------------------------------------------------------
if (isset($_POST['guardar_cambios'])) {

    $idCita = $_POST['idCita'];
    $fecha = $_POST['fecha_cita'];
    $motivo = trim($_POST['motivo_cita']);

    $stmt = $pdo->prepare("
        UPDATE citas
        SET fecha_cita=?, motivo_cita=?
        WHERE idCita=?
    ");
    $stmt->execute([$fecha, $motivo, $idCita]);

    // Obtener idUser para volver a su vista
    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$idCita]);
    $cita = $stmt->fetch();

    $exito = "Cita actualizada correctamente.";
    header("Location: citas-administracion.php?idUser=" . $cita['idUser']);
    exit;
}

// ------------------------------------------------------
// OBTENER idUser SELECCIONADO (siempre correcto)
// ------------------------------------------------------
$idUserSeleccionado = null;

if (isset($_GET['idUser'])) {
    $idUserSeleccionado = $_GET['idUser'];
} elseif (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$_GET['editar']]);
    $idUserSeleccionado = $stmt->fetchColumn();
} elseif (isset($_GET['borrar'])) {
    $stmt = $pdo->prepare("SELECT idUser FROM citas WHERE idCita=?");
    $stmt->execute([$_GET['borrar']]);
    $idUserSeleccionado = $stmt->fetchColumn();
}

// ------------------------------------------------------
// OBTENER CITAS DEL USUARIO SELECCIONADO
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
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <title>Administración de citas</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Panel de administración de citas</h1>

<!-- SELECTOR DE USUARIO -->
<h2>Seleccionar usuario</h2>

<form method="GET">
    <label>Usuario:</label>
    <select name="idUser" required>
        <option value="">-- Selecciona un usuario --</option>
        <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['idUser'] ?>"
                <?= ($idUserSeleccionado == $u['idUser']) ? 'selected' : '' ?>>
                <?= $u['nombre'] . " " . $u['apellidos'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Ver citas</button>
</form>

<hr>

<!-- MENSAJES -->
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

<!-- TABLA DE CITAS DEL USUARIO -->
<?php if ($idUserSeleccionado): ?>

<h2>Citas del usuario seleccionado</h2>

<?php if (!$citas): ?>
    <p>No tiene citas asignadas.</p>
<?php else: ?>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Fecha</th>
        <th>Motivo</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($citas as $c): ?>
        <tr>
            <td><?= $c['idCita'] ?></td>
            <td><?= $c['fecha_cita'] ?></td>
            <td><?= $c['motivo_cita'] ?></td>

            <td>
                <a href="citas-administracion.php?editar=<?= $c['idCita'] ?>&idUser=<?= $c['idUser'] ?>">Editar</a>
                |
                <a href="citas-administracion.php?borrar=<?= $c['idCita'] ?>&idUser=<?= $c['idUser'] ?>" onclick="return confirm('¿Seguro que deseas borrar esta cita?')">Borrar</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php endif; ?>

<!-- FORMULARIO PARA CREAR CITA -->
<hr>
<h2>Crear nueva cita</h2>

<form method="POST" action="crear-cita.php">
    <input type="hidden" name="idUser" value="<?= $idUserSeleccionado ?>">

    <label>Fecha:</label>
    <input type="date" name="fecha_cita" required><br><br>

    <label>Motivo:</label><br>
    <textarea name="motivo_cita" rows="4" cols="50" required></textarea><br><br>

    <button type="submit">Crear cita</button>
</form>

<?php endif; ?>

<!-- FORMULARIO DE EDICIÓN -->
<?php if ($citaEditar): ?>
<hr>
<h2>Editar cita</h2>

<form method="POST">
    <input type="hidden" name="guardar_cambios" value="1">
    <input type="hidden" name="idCita" value="<?= $citaEditar['idCita'] ?>">

    <label>Fecha:</label>
    <input type="date" name="fecha_cita" value="<?= $citaEditar['fecha_cita'] ?>" required><br><br>

    <label>Motivo:</label><br>
    <textarea name="motivo_cita" rows="4" cols="50"><?= $citaEditar['motivo_cita'] ?></textarea><br><br>

    <button type="submit">Guardar cambios</button>
</form>
<?php endif; ?>

</body>
</html>
