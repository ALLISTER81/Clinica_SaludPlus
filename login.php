<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errores = [];
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    if ($usuario === '' || $password === '') {
        $errores[] = "Debes introducir usuario y contraseña.";
    } else {

        // Buscar usuario
        $stmt = $pdo->prepare("
            SELECT ul.*, ud.nombre
            FROM users_login ul
            JOIN users_data ud ON ul.idUser = ud.idUser
            WHERE ul.usuario = ?
        ");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            // Login correcto
            $_SESSION['idUser'] = $user['idUser'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['nombre'] = $user['nombre'];

            $exito = "Inicio de sesión correcto. Redirigiendo...";

            // Redirigir después de 2 segundos
            header("refresh:2; url=index.php");

        } else {
            $errores[] = "Usuario o contraseña incorrectos.";
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
    <title>Iniciar sesión</title>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <h1>Iniciar sesión</h1>

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

    <form method="POST" class="form-login">
        <label>Usuario:</label>
        <input type="text" name="usuario" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>

    <p class="texto-secundario">
        ¿No tienes una cuenta?
        <a href="registro.php">Regístrate aquí</a>
    </p>

    <footer>
        <p>© 2026 Clínica SaludPlus — Todos los derechos reservados</p>
    </footer>

</body>
</html>
