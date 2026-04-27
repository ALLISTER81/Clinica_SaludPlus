<?php
require 'conexion.php';

$idUser = $_POST['idUser'];
$fecha = $_POST['fecha_cita'];
$motivo = $_POST['motivo_cita'];

$stmt = $pdo->prepare("INSERT INTO citas (idUser, fecha_cita, motivo_cita) VALUES (?, ?, ?)");
$stmt->execute([$idUser, $fecha, $motivo]);

header("Location: citas-administracion.php?idUser=$idUser");
exit;
