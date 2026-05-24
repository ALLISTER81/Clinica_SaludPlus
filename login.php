<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errores = [];
$mensaje_exito = $_SESSION['mensaje_exito'] ?? null;
unset($_SESSION['mensaje_exito']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario  = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (login($usuario, $password)) {

        // Mensaje visible en login.php
        $mensaje_exito = "Inicio de sesión correcto. Redirigiendo...";

        // Redirección diferida
        header("Refresh: 2; URL=index.php");

    } else {
        $errores[] = "Usuario o contraseña incorrectos.";
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
    
    <h1 class="public-title">Iniciar sesión</h1>
    
    <!-- MENSAJES-->
    <?php if (!empty($mensaje_exito)): ?>
        <div class="mensaje-exito">
            <?= htmlspecialchars($mensaje_exito) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="mensaje-error">
            <?php foreach ($errores as $e): ?>
                <?= htmlspecialchars($e) ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="public-section">
        <div class="public-container">

            <form method="POST" class="form">

                <label>Usuario:</label>
                <input type="text" name="usuario" required>

                <label>Contraseña:</label>
                <input type="password" name="password" required>

                <button type="submit" class="btn btn-primario">Entrar</button>

                <p class="texto-secundario"">
                    ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
                </p> 

            </form>  
            
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="scripts/mensajes.js"></script>

</body>
</html>
