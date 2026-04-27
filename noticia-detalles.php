<?php
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("
    SELECT n.titulo, n.texto, n.imagen, n.fecha,
           u.nombre, u.apellidos
    FROM noticias n
    JOIN users_data u ON n.idUser = u.idUser
    WHERE n.idNoticia = ?
");
$stmt->execute([$id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <title><?= $noticia['titulo'] ?></title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1><?= $noticia['titulo'] ?></h1>

<img src="uploads/<?= $noticia['imagen'] ?>" class="detalle-img">

<p class="detalle-texto">
    <?= nl2br($noticia['texto']) ?>
</p>

<a href="noticias.php" class="btn-volver">← Volver a noticias</a>

</body>
</html>
