<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$stmt = $pdo->query("
    SELECT n.idNoticia, n.titulo, n.imagen, n.texto, n.fecha,
           u.nombre, u.apellidos
    FROM noticias n
    JOIN users_data u ON n.idUser = u.idUser
    ORDER BY n.fecha DESC
");
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <title>Noticias</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<h1>Noticias</h1>

<?php foreach ($noticias as $n): ?>
    <div class="tarjeta-blog">

        <img src="uploads/<?= $n['imagen'] ?>" class="tarjeta-blog-img">

        <div class="tarjeta-blog-contenido">
            <h3><?= $n['titulo'] ?></h3>

            <span class="tarjeta-blog-fecha">
                <?= date("d/m/Y", strtotime($n['fecha'])) ?>
            </span>

            <span class="tarjeta-blog-autor">
                Publicado por <?= $n['nombre'] . " " . $n['apellidos'] ?>
            </span>

            <p class="tarjeta-blog-texto-completa">
                <?= nl2br($n['texto']) ?>
            </p>

        </div>
    </div>
<?php endforeach; ?>

</body>
</html>
