<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Solo admins
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// Verificar idUser
if (!isset($_GET['idUser'])) {
    die("ID de usuario no especificado.");
}

$idUser = $_GET['idUser'];

$errores = [];
$exito = "";

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

/* ============================================================
   1. CAMBIAR CONTRASEÑA (ADMIN) — SOLO BCRYPT
   ============================================================ */
if (isset($_POST['cambiar_password_admin'])) {

    $passwordNueva  = trim($_POST['password_nueva']);
    $passwordNueva2 = trim($_POST['password_nueva2']);

    if ($passwordNueva !== $passwordNueva2) {
        $errores[] = "Las nuevas contraseñas no coinciden.";
    } else {

        // Hash seguro
        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users_login SET password=? WHERE idUser=?");
        $stmt->execute([$nuevoHash, $idUser]);

        $exito = "Contraseña actualizada correctamente.";
    }
}

/* ============================================================
   2. GUARDAR CAMBIOS DE DATOS PERSONALES
   ============================================================ */
if (isset($_POST['guardar_datos'])) {

    $nombre           = trim($_POST['nombre']);
    $apellidos        = trim($_POST['apellidos']);
    $email            = trim($_POST['email']);
    $telefono         = trim($_POST['telefono']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $direccion        = trim($_POST['direccion']);
    $sexo             = trim($_POST['sexo']);
    $usuario_login    = trim($_POST['usuario']);
    $rol              = trim($_POST['rol']);

    // Validación email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("El email no es válido.");
    }

    // Actualizar users_data
    $stmt = $pdo->prepare("
        UPDATE users_data
        SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=?
        WHERE idUser=?
    ");
    $stmt->execute([
        $nombre, $apellidos, $email, $telefono,
        $fecha_nacimiento, $direccion, $sexo, $idUser
    ]);

    // Actualizar users_login
    $stmt = $pdo->prepare("
        UPDATE users_login
        SET usuario=?, rol=?
        WHERE idUser=?
    ");
    $stmt->execute([$usuario_login, $rol, $idUser]);

    header("Location: usuarios-administracion.php?editado=1");
    exit;
}
?>

<?php 
$pageTitle = "Editar usuario";
$isAdmin = true;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/public.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/navbar.css">

    <title>Editar Usuario</title>    
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<main>

    <h1 class="admin-title">Editar Usuario</h1>

    <section class="admin-section">
        <div class="admin-container">

            <!-- MENSAJES -->
            <?php if (!empty($exito)): ?>
                <div class="mensaje-exito"><?= $exito ?></div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="mensaje-error">
                    <?php foreach ($errores as $e): ?>
                        <?= htmlspecialchars($e) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>


            <!-- ============================================================
                 FORMULARIO 1 — DATOS PERSONALES
            ============================================================ -->
            <h2 class="public-subtitle">Datos personales</h2>

            <form method="POST" class="admin-form">

                <label>Nombre:</label>
                <input type="text" name="nombre" 
                       value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" 
                       value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>

                <label>Email:</label>
                <input type="email" name="email" 
                       value="<?= htmlspecialchars($usuario['email']) ?>" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono" 
                       value="<?= htmlspecialchars($usuario['telefono']) ?>">

                <label>Fecha nacimiento:</label>
                <input type="date" name="fecha_nacimiento" 
                       value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>">

                <label>Dirección:</label>
                <input type="text" name="direccion" 
                       value="<?= htmlspecialchars($usuario['direccion']) ?>">

                <label>Sexo:</label>
                <select name="sexo" required>
                    <option value="masculino" <?= $usuario['sexo']=='masculino'?'selected':'' ?>>Hombre</option>
                    <option value="femenino" <?= $usuario['sexo']=='femenino'?'selected':'' ?>>Mujer</option>
                    <option value="otro" <?= $usuario['sexo']=='otro'?'selected':'' ?>>Otro</option>
                </select>

                <label>Usuario (login):</label>
                <input type="text" name="usuario" 
                       value="<?= htmlspecialchars($usuario['usuario']) ?>" required>

                <label>Rol:</label>
                <select name="rol">
                    <option value="usuario" <?= $usuario['rol']=='usuario'?'selected':'' ?>>Usuario</option>
                    <option value="admin" <?= $usuario['rol']=='admin'?'selected':'' ?>>Administrador</option>
                </select>

                <button type="submit" name="guardar_datos" class="btn btn-primario">
                    Guardar cambios
                </button>

            </form>


            <!-- ============================================================
                 FORMULARIO 2 — CAMBIAR CONTRASEÑA
            ============================================================ -->
            <h2 class="public-subtitle">Cambiar contraseña</h2>

            <form method="POST" class="admin-form">

                <label>Nueva contraseña:</label>
                <input type="password" name="password_nueva" required>

                <label>Repetir nueva contraseña:</label>
                <input type="password" name="password_nueva2" required>

                <button type="submit" name="cambiar_password_admin" class="btn btn-primario">
                    Actualizar contraseña
                </button>

            </form>

            <a href="usuarios-administracion.php" class="btn btn-primario btn-volver">Volver</a>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>

