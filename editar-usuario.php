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
    $sexo = $_POST['sexo'];
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/Trabajo_Final_Php/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Trabajo_Final_Php/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Trabajo_Final_Php/favicon-16x16.png">
    <link rel="manifest" href="/Trabajo_Final_Php/site.webmanifest">
    <link rel="icon" href="/Trabajo_Final_Php/favicon.ico">
    <title>Editar Usuario</title>    
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <section class="admin-section">
        <div class="admin-container">

            <h2 class="admin-title">Editar Usuario</h2>

            <form method="POST" class="admin-form">

                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?= $usuario['nombre'] ?>" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" value="<?= $usuario['apellidos'] ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?= $usuario['email'] ?>" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono" value="<?= $usuario['telefono'] ?>">

                <label>Fecha nacimiento:</label>
                <input type="date" name="fecha_nacimiento" value="<?= $usuario['fecha_nacimiento'] ?>">

                <label>Dirección:</label>
                <input type="text" name="direccion" value="<?= $usuario['direccion'] ?>">

                <label>Sexo:</label>
                <select name="sexo" required>
                    <option value="masculino" <?= $usuario['sexo']=='masculino'?'selected':'' ?>>Hombre</option>
                    <option value="femenino" <?= $usuario['sexo']=='femenino'?'selected':'' ?>>Mujer</option>
                    <option value="otro" <?= $usuario['sexo']=='otro'?'selected':'' ?>>Otro</option>
                </select>

                <label>Usuario (login):</label>
                <input type="text" name="usuario" value="<?= $usuario['usuario'] ?>" required>

                <label>Rol:</label>
                    <select name="rol">
                        <option value="usuario" <?= $usuario['rol']=='usuario'?'selected':'' ?>>Usuario</option>
                        <option value="admin" <?= $usuario['rol']=='admin'?'selected':'' ?>>Administrador</option>
                    </select>

                <button type="submit" class="btn-guardar">Guardar cambios</button>
            </form>

            <a href="usuarios-administracion.php" class="btn-volver">← Volver</a>

         </div>
    </section>

    <footer>
        <p>© 2026 Clínica SaludPlus — Todos los derechos reservados</p>
    </footer>

</body>
</html>
