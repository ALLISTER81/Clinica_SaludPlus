<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si hay un usuario logueado
 */
function isLogged() {
    return isset($_SESSION['idUser']);
}

/**
 * Verifica si el usuario logueado es administrador
 */
function isAdmin() {
    return isLogged() && isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

/**
 * Iniciar sesión
 */
function login($usuario, $password) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT ul.idUser, ul.password, ul.rol, ud.nombre
        FROM users_login ul
        JOIN users_data ud ON ul.idUser = ud.idUser
        WHERE ul.usuario = ?
    ");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false;
    }

    $storedHash = $user['password'];

    // 1) Si el hash empieza por '$' → asumimos bcrypt/password_hash()
    if (strpos($storedHash, '$') === 0) {

        // Verificación moderna
        if (!password_verify($password, $storedHash)) {
            return false;
        }

    } else {

        // 2) Compatibilidad con hashes antiguos SHA-256 + SALT fijo
        $salt = "SALUDPLUS_SALT_2025";
        $legacyHash = hash('sha256', $password . $salt);

        if (!hash_equals($storedHash, $legacyHash)) {
            return false;
        }

        // Si la contraseña antigua es correcta → migrar a bcrypt
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare("
            UPDATE users_login
            SET password = ?
            WHERE idUser = ?
        ");
        $update->execute([$newHash, $user['idUser']]);

        // A partir de ahora, ese usuario ya usa bcrypt
    }

    // Login correcto → guardar sesión
    $_SESSION['idUser'] = $user['idUser'];
    $_SESSION['rol']    = $user['rol'];
    $_SESSION['nombre'] = $user['nombre'];

    return true;
}

/**
 * Registrar un nuevo usuario
 */
function registrar($nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo, $rol, $usuario, $password) {
    global $pdo;

    try {
        // Insertar en users_data
        $stmt = $pdo->prepare("
            INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo]);

        $idUser = $pdo->lastInsertId();

        // NUEVO: hash seguro con password_hash()
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar en users_login
        $stmt = $pdo->prepare("
            INSERT INTO users_login (idUser, usuario, password, rol)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$idUser, $usuario, $passwordHash, $rol]);

        return true;

    } catch (PDOException $e) {

        if ($e->getCode() == 23000) {
            return false; // email o usuario duplicado
        }

        throw $e;
    }
}

/**
 * Cerrar sesión
 */
function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}
