<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errores = [];
$exito = '';

// Mensajes después de editar usuario
if (isset($_GET['editado'])) {
    $exito = "Usuario actualizado correctamente.";
}


// Solo admins
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// Crear usuario
if (isset($_POST['crear'])) {

    $nombre           = $_POST['nombre'];
    $apellidos        = $_POST['apellidos'];
    $email            = trim($_POST['email']);
    $telefono         = $_POST['telefono'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion        = $_POST['direccion'];
    $sexo             = $_POST['sexo'];

    $usuario  = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol      = $_POST['rol'];

    // Validación del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no es válido.";
    }

    // Validación del usuario (login)
    if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}/', $usuario)) {
        $errores[] = "El usuario debe incluir mayúscula, minúscula, número y carácter especial (mínimo 6 caracteres).";
    }

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
        $pdo->prepare("DELETE FROM users_data  WHERE idUser = ?")->execute([$id]);

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

<?php 
$pageTitle = "Administración de Usuarios";
$isAdmin = true;
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
        <h1 class="admin-title">Administración de usuarios</h1>
    </section>

    <!-- FORMULARIO DE CREACIÓN -->
    <section class="admin-section">
        <div class="admin-container">

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

            <h2>Crear nuevo usuario</h2>

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

                <button type="submit" name="crear" class="btn btn-primario">Crear usuario</button>
            </form>

        </div>
    </section>

    <!-- LISTADO DE USUARIOS -->
    <section class="admin-section">

        <h2 class="admin-subtitle" style="text-align:center;">Usuarios existentes</h2>

        <div class="admin-table-wrapper">
            <table class="admin-table">

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
                    <td><?= htmlspecialchars($u['nombre'] . " " . $u['apellidos']) ?></td>
                    <td><?= htmlspecialchars($u['usuario']) ?></td>
                    <td><?= htmlspecialchars($u['rol']) ?></td>
                    <td>
                        <a class="btn-accion btn-editar" href="editar-usuario.php?idUser=<?= $u['idUser'] ?>">Editar</a>

                        <?php if ($u['idUser'] != $_SESSION['idUser']): ?>
                            <a class="btn-accion btn-borrar"
                            href="usuarios-administracion.php?borrar=<?= $u['idUser'] ?>"
                            onclick="return confirm('¿Seguro que deseas borrar este usuario?')">
                                Borrar
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
