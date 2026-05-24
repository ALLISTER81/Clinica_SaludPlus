<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLogged()) {
    header("Location: login.php");
    exit;
}

$idUser = $_SESSION['idUser'];

$exito = '';
$errores = [];

/* ============================================================
   1. CARGAR DATOS DEL USUARIO (users_data)
   ============================================================ */
$stmt = $pdo->prepare("SELECT * FROM users_data WHERE idUser=?");
$stmt->execute([$idUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$nombre = $user['nombre'];
$apellidos = $user['apellidos'];
$email = $user['email']; // Editable
$telefono = $user['telefono'];
$fecha_nacimiento = $user['fecha_nacimiento'];
$direccion = $user['direccion'];
$sexo = $user['sexo'];

/* ============================================================
   1b. CARGAR USUARIO (users_login)
   ============================================================ */
$stmt = $pdo->prepare("SELECT usuario FROM users_login WHERE idUser=?");
$stmt->execute([$idUser]);
$userLogin = $stmt->fetch(PDO::FETCH_ASSOC);

$usuario = $userLogin['usuario']; // NO editable

/* ============================================================
   2. ACTUALIZAR DATOS PERSONALES
   ============================================================ */
if (isset($_POST['guardar_datos'])) {

    $stmt = $pdo->prepare("
        UPDATE users_data
        SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=?
        WHERE idUser=?
    ");

    $stmt->execute([
        $_POST['nombre'],
        $_POST['apellidos'],
        $_POST['email'],
        $_POST['telefono'],
        $_POST['fecha_nacimiento'],
        $_POST['direccion'],
        $_POST['sexo'],
        $idUser
    ]);

    $exito = "Datos actualizados correctamente.";

    // Recargar datos actualizados
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['direccion'];
    $sexo = $_POST['sexo'];
}


/* ============================================================
   3. CAMBIAR CONTRASEÑA (solo bcrypt)
   ============================================================ */
if (isset($_POST['cambiar_password'])) {

    // Obtener hash actual almacenado
    $stmt = $pdo->prepare("SELECT password FROM users_login WHERE idUser=?");
    $stmt->execute([$idUser]);
    $userLogin = $stmt->fetch(PDO::FETCH_ASSOC);

    $storedHash = $userLogin['password'];
    $passwordActual = $_POST['password_actual'];
    $passwordNueva  = $_POST['password_nueva'];
    $passwordNueva2 = $_POST['password_nueva2'];

    /* ------------------------------------------------------------
       3.1 Verificar contraseña actual (bcrypt)
       ------------------------------------------------------------ */
    if (!password_verify($passwordActual, $storedHash)) {
        $errores[] = "La contraseña actual no es correcta.";
    } elseif ($passwordNueva !== $passwordNueva2) {
        $errores[] = "Las nuevas contraseñas no coinciden.";
    } else {

        /* ------------------------------------------------------------
           3.2 Guardar nueva contraseña en bcrypt
           ------------------------------------------------------------ */
        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users_login SET password=? WHERE idUser=?");
        $stmt->execute([$nuevoHash, $idUser]);

        $exito = "Contraseña actualizada correctamente.";
    }
}
?>


<?php 
$pageTitle = "Perfil";
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

    <h1 class="public-title">Perfil</h1>

    <section class="public-section">
        <div class="public-container">

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

            <h2 class="public-subtitle">Datos personales</h2>

            <form method="POST" class="form">

                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" value="<?= htmlspecialchars($apellidos) ?>" required>

                <label>Usuario (no editable):</label>
                <input type="text" value="<?= htmlspecialchars($usuario) ?>" disabled>

                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono" value="<?= htmlspecialchars($telefono) ?>" required>

                <label>Fecha de nacimiento:</label>
                <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($fecha_nacimiento) ?>" required>

                <label>Dirección:</label>
                <input type="text" name="direccion" value="<?= htmlspecialchars($direccion) ?>" required>

                <label>Sexo:</label>
                <select name="sexo" required>
                    <option value="masculino" <?= $sexo=='masculino'?'selected':'' ?>>Masculino</option>
                    <option value="femenino" <?= $sexo=='femenino'?'selected':'' ?>>Femenino</option>
                    <option value="otro" <?= $sexo=='otro'?'selected':'' ?>>Otro</option>
                </select>

                <button type="submit" name="guardar_datos" class="btn btn-primario">
                    Guardar cambios
                </button>

            </form>

            <h2 class="public-subtitle">Cambiar contraseña</h2>

            <form method="POST" class="form">

                <label>Contraseña actual:</label>
                <input type="password" name="password_actual" required>

                <label>Nueva contraseña:</label>
                <input type="password" name="password_nueva" required>

                <label>Repetir nueva contraseña:</label>
                <input type="password" name="password_nueva2" required>

                <button type="submit" name="cambiar_password" class="btn btn-primario">
                    Actualizar contraseña
                </button>

            </form>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="scripts/mensajes.js"></script>

</body>
</html>
