<?php
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("
    SELECT n.titulo, n.texto, n.imagen, n.fecha,
           u.nombre, u.apellidos
    FROM noticias n
    LEFT JOIN users_data u ON n.idUser = u.idUser
    WHERE n.idNoticia = ?
");
$stmt->execute([$id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php 
$pageTitle = $noticia['titulo'];
$isAdmin = false;
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
        <h1><?= $noticia['titulo'] ?></h1>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="admin-section">
        <div class="container detalle-contenedor">

            <p class="detalle-meta">
                Publicado el <?= date("d/m/Y", strtotime($noticia['fecha'])) ?>
                · Por <?= $noticia['nombre'] . " " . $noticia['apellidos'] ?>
            </p>

            <img src="uploads/<?= $noticia['imagen'] ?>" class="detalle-img">

            <div class="detalle-texto">
                <?= nl2br($noticia['texto']) ?>
            </div>

            <a href="noticias.php" class="btn btn-primario btn-volver">← Volver a noticias</a>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
