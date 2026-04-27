<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Si no está logueado, fuera
if (!isLogged()) {
    header("Location: login.php");
    exit;
}

$errores = [];
$exito = '';
$idUser = $_SESSION['idUser'];

// Obtener datos del usuario
$stmt = $pdo->prepare("
    SELECT ud.*, ul.usuario 
    FROM users_data ud
    JOIN users_login ul ON ud.idUser = ul.idUser
    WHERE ud.idUser = ?
");
$stmt->execute([$idUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si envía formulario de datos personales
if (isset($_POST['actualizar_datos'])) {

    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = trim($_POST['direccion']);

    // VALIDACIÓN CORRECTA DEL SEXO
    $sexo = $_POST['sexo'] ?? null;

    if (!in_array($sexo, ['masculino', 'femenino', 'otro'])) {
        $sexo = 'otro'; // valor por defecto si no se selecciona nada
    }

    if ($nombre === '' || $apellidos === '' || $email === '' || $telefono === '' || $fecha_nacimiento === '') {
        $errores[] = "Todos los campos obligatorios deben estar completos.";
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("
            UPDATE users_data
            SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=?
            WHERE idUser=?
        ");
        $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo, $idUser]);

        $exito = "Datos actualizados correctamente.";
    }
}

// Si envía formulario de cambio de contraseña
if (isset($_POST['cambiar_password'])) {

    $actual = $_POST['password_actual'];
    $nueva = $_POST['password_nueva'];
    $repetir = $_POST['password_repetir'];

    // Obtener contraseña actual
    $stmt = $pdo->prepare("SELECT password FROM users_login WHERE idUser = ?");
    $stmt->execute([$idUser]);
    $passDB = $stmt->fetchColumn();

    if (!password_verify($actual, $passDB)) {
        $errores[] = "La contraseña actual no es correcta.";
    } elseif ($nueva !== $repetir) {
        $errores[] = "Las contraseñas nuevas no coinciden.";
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users_login SET password=? WHERE idUser=?");
        $stmt->execute([$hash, $idUser]);

        $exito = "Contraseña actualizada correctamente.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <title>Perfil</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Mi perfil</h1>

<?php if (!empty($errores)): ?>
    <div style="color:red;">
        <?php foreach ($errores as $e): ?>
            <p><?= $e ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($exito): ?>
    <div style="color:green;">
        <p><?= $exito ?></p>
    </div>
<?php endif; ?>

<h2>Datos personales</h2>

<form method="POST">
    <input type="hidden" name="actualizar_datos" value="1">

    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= $user['nombre'] ?>" required><br>

    <label>Apellidos:</label>
    <input type="text" name="apellidos" value="<?= $user['apellidos'] ?>" required><br>

    <label>Email:</label>
    <input type="email" name="email" value="<?= $user['email'] ?>" required><br>

    <label>Teléfono:</label>
    <input type="text" name="telefono" value="<?= $user['telefono'] ?>" required><br>

    <label>Fecha de nacimiento:</label>
    <input type="date" name="fecha_nacimiento" value="<?= $user['fecha_nacimiento'] ?>" required><br>

    <label>Dirección:</label>
    <input type="text" name="direccion" value="<?= $user['direccion'] ?>"><br>

    <label>Sexo:</label>
    <select name="sexo" required>
        <option value="" disabled <?= empty($user['sexo'])?'selected':'' ?>>Selecciona</option>
        <option value="masculino" <?= $user['sexo']=='masculino'?'selected':'' ?>>Masculino</option>
        <option value="femenino" <?= $user['sexo']=='femenino'?'selected':'' ?>>Femenino</option>
        <option value="otro" <?= $user['sexo']=='otro'?'selected':'' ?>>Otro</option>
    </select><br>

    <label>Usuario (no modificable):</label>
    <input type="text" value="<?= $user['usuario'] ?>" disabled><br>

    <button type="submit">Guardar cambios</button>
</form>

<hr>

<h2>Cambiar contraseña</h2>

<form method="POST">
    <input type="hidden" name="cambiar_password" value="1">

    <label>Contraseña actual:</label>
    <input type="password" name="password_actual" required><br>

    <label>Nueva contraseña:</label>
    <input type="password" name="password_nueva" required><br>

    <label>Repetir nueva contraseña:</label>
    <input type="password" name="password_repetir" required><br>

    <button type="submit">Cambiar contraseña</button>
</form>

</body>
</html>
