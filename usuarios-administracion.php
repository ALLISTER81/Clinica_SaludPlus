<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Solo admins
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// Crear usuario
if (isset($_POST['crear'])) {

    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['direccion'];
    $sexo = $_POST['sexo'];

    $usuario = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    // Insertar en users_data
    $stmt = $pdo->prepare("
        INSERT INTO users_data 
        (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo]);

    $idUser = $pdo->lastInsertId();

    // Insertar en users_login
    $stmt = $pdo->prepare("
        INSERT INTO users_login 
        (idUser, usuario, password, rol)
        VALUES (?, ?, ?, ?)
    ");
  $stmt->execute([$idUser, $usuario, $password, $rol]);
}

// Borrar usuario
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];

    // No permitir borrar al propio admin
    if ($id != $_SESSION['idUser']) {

        $pdo->prepare("DELETE FROM users_login WHERE idUser = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM users_data WHERE idUser = ?")->execute([$id]);
    }
}

// Obtener usuarios
$stmt = $pdo->query("
    SELECT ud.idUser, ud.nombre, ud.apellidos, ul.usuario, ul.rol
    FROM users_data ud
    JOIN users_login ul ON ud.idUser = ul.idUser
");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Usuarios admin</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Administración de usuarios</h1>

<h2>Crear nuevo usuario</h2>

<form method="POST">
   
    <label>Nombre:</label>
    <input type="text" name="nombre" required>

    <label>Apellidos:</label>
    <input type="text" name="apellidos" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Teléfono:</label>
    <input type="text" name="telefono" required>

    <label>Fecha nacimiento:</label>
    <input type="date" name="fecha_nacimiento" required>

    <label>Dirección:</label>
    <input type="text" name="direccion" required>

    <label>Sexo:</label>
    <select name="sexo">
        <option value="masculino">Masculino</option>
        <option value="femenino">Femenino</option>
    </select>

    <label>Usuario (login):</label>
    <input type="text" name="usuario" required>

    <label>Contraseña:</label>
    <input type="password" name="password" required>

    <label>Rol:</label>
    <select name="rol">
        <option value="usuario">Usuario</option>
        <option value="admin">Administrador</option>
    </select>

    <button type="submit" name="crear">Crear usuario</button>
</form>

<h2>Usuarios existentes</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Usuario</th>
        <th>Rol</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= $u['idUser'] ?></td>
            <td><?= $u['nombre'] . " " . $u['apellidos'] ?></td>
            <td><?= $u['usuario'] ?></td>
            <td><?= $u['rol'] ?></td>
            <td>
                <a href="editar-usuario.php?idUser=<?= $u['idUser'] ?>">Editar</a>
                <?php if ($u['idUser'] != $_SESSION['idUser']): ?>
                    | <a href="?borrar=<?= $u['idUser'] ?>" onclick="return confirm('¿Seguro?')">Borrar</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
