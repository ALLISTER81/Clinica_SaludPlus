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
   1. CARGAR DATOS DEL USUARIO
   ============================================================ */
$stmt = $pdo->prepare("SELECT * FROM users_data WHERE idUser=?");
$stmt->execute([$idUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$nombre = $user['nombre'];
$apellidos = $user['apellidos'];
$email = $user['email']; // NO editable
$telefono = $user['telefono'];
$fecha_nacimiento = $user['fecha_nacimiento'];
$direccion = $user['direccion'];
$sexo = $user['sexo'];

/* ============================================================
   2. ACTUALIZAR DATOS PERSONALES
   ============================================================ */
if (isset($_POST['guardar_datos'])) {

    $stmt = $pdo->prepare("
        UPDATE users_data
        SET nombre=?, apellidos=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=?
        WHERE idUser=?
    ");

    $stmt->execute([
        $_POST['nombre'],
        $_POST['apellidos'],
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
    $telefono = $_POST['telefono'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['direccion'];
    $sexo = $_POST['sexo'];
}

/* ============================================================
   3. CAMBIAR CONTRASEÑA
   ============================================================ */
if (isset($_POST['cambiar_password'])) {

    // Obtener contraseña actual
    $stmt = $pdo->prepare("SELECT password FROM users_login WHERE idUser=?");
    $stmt->execute([$idUser]);
    $userLogin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validar contraseña actual
    if (!password_verify($_POST['password_actual'], $userLogin['password'])) {
        $errores[] = "La contraseña actual no es correcta.";
    } elseif ($_POST['password_nueva'] !== $_POST['password_nueva2']) {
        $errores[] = "Las nuevas contraseñas no coinciden.";
    } else {

        // Actualizar contraseña
        $stmt = $pdo->prepare("UPDATE users_login SET password=? WHERE idUser=?");
        $stmt->execute([
            password_hash($_POST['password_nueva'], PASSWORD_DEFAULT),
            $idUser
        ]);

        $exito = "Contraseña actualizada correctamente.";
    }
}
?>


<?php 
$pageTitle = "Mi perfil";
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
        <h1 class="admin-title">Mi Perfil</h1>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="admin-section">
        <div class="container">

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
                 FORMULARIO 1: DATOS PERSONALES
                 ============================================================ -->
            <h2>Datos personales</h2>

            <form method="POST" class="form">

                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

                <label>Apellidos:</label>
                <input type="text" name="apellidos" value="<?= htmlspecialchars($apellidos) ?>" required>

                <label>Email (no editable):</label>
                <input type="email" value="<?= htmlspecialchars($email) ?>" disabled>

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


            <!-- ============================================================
                 FORMULARIO 2: CAMBIAR CONTRASEÑA
                 ============================================================ -->
            <h2>Cambiar contraseña</h2>

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

