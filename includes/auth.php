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

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['idUser'] = $user['idUser'];
        $_SESSION['rol']    = $user['rol'];
        $_SESSION['nombre'] = $user['nombre'];

        return true;
    }

    return false;
}

/**
 * Registrar un nuevo usuario
 */
function registrar($nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo, $rol, $password) {
    global $pdo;

    // 1. Comprobar si el usuario (email) ya existe en users_login
    $stmt = $pdo->prepare("SELECT idUser FROM users_login WHERE usuario = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        return false; // Usuario ya registrado
    }

    // 2. Insertar en users_data
    $stmt = $pdo->prepare("
        INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo]);

    // 3. Obtener idUser recién creado
    $idUser = $pdo->lastInsertId();

    // 4. Insertar en users_login (usuario = email)
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users_login (idUser, usuario, password, rol)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$idUser, $email, $passwordHash, $rol]);

    return true;
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
