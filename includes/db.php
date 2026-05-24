<?php
$host = "sql309.infinityfree.com";
$db   = "if0_41947797_clinica_SaludPlus_db";
$user = "if0_41947797";
$pass = "8F6yxzAZPt";

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
