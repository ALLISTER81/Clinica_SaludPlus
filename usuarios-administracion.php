<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errores = [];
$exito = '';

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

    // VALIDACIÓN DEL USUARIO (login)
    if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}/', $usuario)) {
        $errores[] = "El usuario debe incluir mayúscula, minúscula, número y carácter especial (mínimo 6 caracteres).";
    }

    // SI HAY ERRORES → NO SE CREA EL USUARIO
    if (empty($errores)) {

        try {

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

            $exito = "Usuario creado correctamente.";

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $errores[] = "El email o el usuario ya están registrados.";
            } else {
                $errores[] = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
}


// Borrar usuario
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];

    // No permitir borrar al propio admin
    if ($id != $_SESSION['idUser']) {

        $pdo->prepare("DELETE FROM users_login WHERE idUser = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM users_data WHERE idUser = ?")->execute([$id]);

        $exito = "Usuario eliminado correctamente.";
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
    <link rel="stylesheet" href="css/styles.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/Trabajo_Final_Php/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Trabajo_Final_Php/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Trabajo_Final_Php/favicon-16x16.png">
    <link rel="manifest" href="/Trabajo_Final_Php/site.webmanifest">
    <link rel="icon" href="/Trabajo_Final_Php/favicon.ico">
    <title>Usuarios admin</title>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <h1>Administración de usuarios</h1>

    <?php if (!empty($errores)): ?>
        <div class="mensaje-error">
            <?php foreach ($errores as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
        <div class="mensaje-exito">
            <p><?= htmlspecialchars($exito) ?></p>
        </div>
    <?php endif; ?>


    <h2 class="seccion-titulo">Crear nuevo usuario</h2>

    <form method="POST" class="admin-form">
   
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
            <select name="sexo" required>
                <option value="" disabled selected>Selecciona</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
                <option value="otro">Otro</option>
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

    <table class="admin-table">

        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th class="acciones">Acciones</th>
        </tr>

        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= $u['idUser'] ?></td>
            <td><?= $u['nombre'] . " " . $u['apellidos'] ?></td>
            <td><?= $u['usuario'] ?></td>
            <td><?= $u['rol'] ?></td>

            <td class="acciones">
                <a class="btn-edit" href="editar-usuario.php?idUser=<?= $u['idUser'] ?>">Editar</a>

                <?php if ($u['idUser'] != $_SESSION['idUser']): ?>
                    <a class="btn-delete" href="?borrar=<?= $u['idUser'] ?>" onclick="return confirm('¿Seguro?')">Borrar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <footer>
        <p>© 2026 Clínica SaludPlus — Todos los derechos reservados</p>
    </footer>


</body>
</html>
