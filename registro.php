<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errores = [];
$exito = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = trim($_POST['nombre']);
    $apellidos= trim($_POST['apellidos']);
    $email    = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $fecha_n  = $_POST['fecha_nacimiento'];
    $direccion= trim($_POST['direccion']);
    $sexo     = $_POST['sexo'];
    $usuario  = trim($_POST['usuario']);
    $password = $_POST['password'];
    $password2= $_POST['password2'];

    // Validaciones básicas
    if ($password !== $password2) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Comprobar email y usuario únicos
    $stmt = $pdo->prepare("
        SELECT 1 FROM users_data WHERE email = ?
        UNION
        SELECT 1 FROM users_login WHERE usuario = ?
    ");
    $stmt->execute([$email, $usuario]);

    if ($stmt->fetch()) {
        $errores[] = "El email o el usuario ya están registrados.";
    }

    if (empty($errores)) {
        try {
            $pdo->beginTransaction();

            // Insertar en users_data
            $stmt = $pdo->prepare("
                INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo)
                VALUES (?,?,?,?,?,?,?)
            ");
            $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_n, $direccion, $sexo]);

            $idUser = $pdo->lastInsertId();

            // Insertar en users_login
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users_login (idUser, usuario, password, rol)
                VALUES (?,?,?, 'usuario')
            ");

            $stmt->execute([$idUser, $usuario, $hash]);

            $pdo->commit();

            $exito = "Registro completado. Serás redirigido al login.";
            header("refresh:3;url=login.php");

        } catch (Exception $e) {
            $pdo->rollBack();
            $errores[] = "Error al registrar. Inténtalo de nuevo.";
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/Trabajo_Final_Php/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Trabajo_Final_Php/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Trabajo_Final_Php/favicon-16x16.png">
    <link rel="manifest" href="/Trabajo_Final_Php/site.webmanifest">
    <link rel="icon" href="/Trabajo_Final_Php/favicon.ico">
    <title>Registro</title>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <h1>Registro de usuario</h1>

    <!-- Si el usuario ya está logueado, redirigir a index -->
        <?php if (!empty($errores)): ?>
            <div class="mensaje-error">
                <?php foreach ($errores as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div class="mensaje-exito">
                <p><?= htmlspecialchars($exito) ?></p>
            </div>
        <?php endif; ?>

    <form method="POST" class="form-registro">
        <label>Nombre:</label>
        <input type="text" name="nombre" required><br>

        <label>Apellidos:</label>
        <input type="text" name="apellidos" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Teléfono:</label>
        <input type="text" name="telefono" required><br>

        <label>Fecha de nacimiento:</label>
        <input type="date" name="fecha_nacimiento" required><br>

        <label>Dirección:</label>
        <input type="text" name="direccion"><br>

        <label>Sexo:</label>
            <select name="sexo" required>
                <option value="">Selecciona</option>
                <option value="masculino">Hombre</option>
                <option value="femenino">Mujer</option>
                <option value="otro">Otro</option>
            </select>
        <br>

        <label>Usuario:</label>
        <input type="text" name="usuario" required><br>

        <label>Contraseña:</label>
        <input type="password" name="password" required><br>

        <label>Repetir contraseña:</label>
        <input type="password" name="password2" required><br>

        <button type="submit">Registrarse</button>
    </form>    

    <footer>
        <p>© 2026 Clínica SaludPlus — Todos los derechos reservados</p>
    </footer>

</body>
</html>
