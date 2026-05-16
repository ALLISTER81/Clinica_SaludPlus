<?php
$host = 'localhost';
$db   = 'clinica_saludPlus';
$user = 'root';
$pass = 'allister';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Error de conexión a la base de datos");
}
