<?php
require 'conexion.php';

try {
    $idUser = $_POST['idUser'] ?? null;
    $fecha = $_POST['fecha_cita'] ?? null;
    $motivo = $_POST['motivo_cita'] ?? null;

    if (!$idUser || !$fecha || !$motivo) {
        throw new Exception("Faltan datos obligatorios");
    }

    // VALIDACIÓN DE FECHA
    $hoy = date('Y-m-d');
    if ($fecha < $hoy) {
        throw new Exception("No se pueden crear citas en fechas pasadas.");
    }

    $stmt = $pdo->prepare("INSERT INTO citas (idUser, fecha_cita, motivo_cita) VALUES (?, ?, ?)");
    $stmt->execute([$idUser, $fecha, $motivo]);

    header("Location: citas-administracion.php?idUser=$idUser&exito=Cita creada correctamente");
    exit;

} catch (Exception $e) {
    $mensaje = urlencode($e->getMessage());
    header("Location: citas-administracion.php?idUser=$idUser&error=$mensaje");
    exit;
}
