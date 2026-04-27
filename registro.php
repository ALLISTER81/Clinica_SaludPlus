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
                VALUES (?,?,?, 'user')
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
    <title>Registro</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Registro de usuario</h1>

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

<form method="POST">
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
    <select name="sexo">
        <option value="">Selecciona</option>
        <option value="hombre">Hombre</option>
        <option value="mujer">Mujer</option>
        <option value="otro">Otro</option>
    </select><br>

    <label>Usuario:</label>
    <input type="text" name="usuario" required><br>

    <label>Contraseña:</label>
    <input type="password" name="password" required><br>

    <label>Repetir contraseña:</label>
    <input type="password" name="password2" required><br>

    <button type="submit">Registrarse</button>
</form>

</body>
</html>
