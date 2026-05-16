<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errores = [];
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $rol = trim($_POST['rol'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password2 = trim($_POST['password2'] ?? '');

    // Validación de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no es válido.";
    }

    // Validación de contraseñas
    if ($password !== $password2) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    if (empty($errores)) {

        if (registrar(
            $nombre,
            $apellidos,
            $email,
            $telefono,
            $fecha_nacimiento,
            $direccion,
            $sexo,
            $rol,
            $password
        )) {

            // REDIRECCIÓN INMEDIATA Y FUNCIONAL
            header("Location: login.php");
            exit;

        } else {
            $errores[] = "El email ya está registrado.";
        }
    }
}
?>

<?php 
$pageTitle = "Registro";
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
        <h1 class="admin-title">Registro de paciente</h1>
    </section>

    <!-- CONTENIDO -->
    <section class="admin-section">
        <div class="page-wrapper">

            <!-- MENSAJES -->
            <?php if (!empty($errores)): ?>
                <div class="mensaje-error">
                    <?php foreach ($errores as $e): ?>
                        <?= htmlspecialchars($e) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($exito)): ?>
                <div class="mensaje-exito">
                    <?= htmlspecialchars($exito) ?>
                </div>
            <?php endif; ?>

            <!-- FORMULARIO -->
            <form method="POST" class="form">

                <label>Nombre:</label>
                <input type="text" name="nombre" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" required>

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono" required>

                <label>Fecha de nacimiento:</label>
                <input type="date" name="fecha_nacimiento" required>

                <label>Dirección:</label>
                <input type="text" name="direccion" required>

                <label>Sexo:</label>
                <select name="sexo" required>
                    <option value="" disabled selected>Selecciona</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    <option value="otro">Otro</option>
                </select>

                <label>Rol:</label>
                <select name="rol" required>
                    <option value="" disabled selected>Selecciona</option>
                    <option value="usuario">Usuario</option>
                    <option value="admin">Administrador</option>
                </select>

                <label>Contraseña:</label>
                <input type="password" name="password" required>

                <label>Repetir contraseña:</label>
                <input type="password" name="password2" required>

                <button type="submit" class="btn btn-primario">Registrarse</button>
            </form>

            <p class="form-link">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
            </p>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="scripts/mensajes.js"></script>

</body>
</html>


