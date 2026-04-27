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
    <title>Iniciar sesión</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Iniciar sesión</h1>

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

<form method="POST">
    <label>Usuario:</label>
    <input type="text" name="usuario" required>

    <label>Contraseña:</label>
    <input type="password" name="password" required>

    <button type="submit">Entrar</button>
</form>

</body>
</html>
