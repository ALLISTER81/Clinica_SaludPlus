<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errores = [];
$mensaje_exito = null;

// Si viene un mensaje de éxito desde otra página (registro, logout, etc.)
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje_exito = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (login($email, $password)) {

        // Mostrar mensaje en login.php antes de redirigir
        $mensaje_exito = "Inicio de sesión correcto. Redirigiendo...";

        // Redirige después de 2 segundos
        header("refresh:2; url=index.php");

    } else {
        $errores[] = "Correo o contraseña incorrectos.";
    }
}
?>

<?php 
$pageTitle = "Login";
$isAdmin = false;
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
        <h1 class="admin-title">Iniciar sesión</h1>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="admin-section">
        <div class="admin-container">

            <!-- MENSAJE DE ÉXITO -->
            <?php if (!empty($mensaje_exito)): ?>
                <div class="mensaje-exito">
                    <?= htmlspecialchars($mensaje_exito) ?>
                </div>
            <?php endif; ?>

            <!-- MENSAJE DE ERROR -->
            <?php if (!empty($errores)): ?>
                <div class="mensaje-error">
                    <?php foreach ($errores as $e): ?>
                        <?= htmlspecialchars($e) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form">

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Contraseña:</label>
                <input type="password" name="password" required>

                <button type="submit" class="btn btn-primario">Entrar</button>

            </form>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="scripts/mensajes.js"></script>

</body>
</html>
