<?php
session_start();
require 'conexion.php';

// Verificar si llega el idUser
if (!isset($_GET['idUser'])) {
    die("ID de usuario no especificado.");
}

$idUser = $_GET['idUser'];

// Obtener datos del usuario
$stmt = $pdo->prepare("
    SELECT ud.*, ul.usuario, ul.rol 
    FROM users_data ud
    JOIN users_login ul ON ud.idUser = ul.idUser
    WHERE ud.idUser = ?
");
$stmt->execute([$idUser]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['direccion'];
    $sexo = $_POST['sexo']; // ahora coincide con ENUM
    $usuario_login = $_POST['usuario'];
    $rol = $_POST['rol'];

    // Actualizar users_data
    $stmt = $pdo->prepare("
        UPDATE users_data
        SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=?
        WHERE idUser=?
    ");
    $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo, $idUser]);

    // Actualizar users_login
    $stmt = $pdo->prepare("
        UPDATE users_login
        SET usuario=?, rol=?
        WHERE idUser=?
    ");
    $stmt->execute([$usuario_login, $rol, $idUser]);

    header("Location: usuarios-administracion.php?edit=ok");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="css/styles-editar.css">
</head>
<body>

<div class="contenedor">
<h2>Editar Usuario</h2>

<form method="POST">

    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= $usuario['nombre'] ?>" required><br>

    <label>Apellidos:</label>
    <input type="text" name="apellidos" value="<?= $usuario['apellidos'] ?>" required><br>

    <label>Email:</label>
    <input type="email" name="email" value="<?= $usuario['email'] ?>" required><br>

    <label>Teléfono:</label>
    <input type="text" name="telefono" value="<?= $usuario['telefono'] ?>"><br>

    <label>Fecha nacimiento:</label>
    <input type="date" name="fecha_nacimiento" value="<?= $usuario['fecha_nacimiento'] ?>"><br>

    <label>Dirección:</label>
    <input type="text" name="direccion" value="<?= $usuario['direccion'] ?>"><br>

    <label>Sexo:</label>
    <select name="sexo" required>
        <option value="masculino" <?= $usuario['sexo']=='masculino'?'selected':'' ?>>Hombre</option>
        <option value="femenino" <?= $usuario['sexo']=='femenino'?'selected':'' ?>>Mujer</option>
        <option value="otro" <?= $usuario['sexo']=='otro'?'selected':'' ?>>Otro</option>
    </select><br>

    <label>Usuario (login):</label>
    <input type="text" name="usuario" value="<?= $usuario['usuario'] ?>" required><br>

    <label>Rol:</label>
    <select name="rol">
        <option value="usuario" <?= $usuario['rol']=='usuario'?'selected':'' ?>>Usuario</option>
        <option value="admin" <?= $usuario['rol']=='admin'?'selected':'' ?>>Administrador</option>
    </select><br>

    <button type="submit" class="boton-guardar">Guardar cambios</button>
</form>

<a href="usuarios-administracion.php" class="boton-volver">← Volver</a>
</div>

</body>
</html>
