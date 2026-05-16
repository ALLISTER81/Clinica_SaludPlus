<?php
require_once 'includes/db.php';

$stmt = $pdo->query("
    SELECT idNoticia, titulo, imagen, texto, fecha
    FROM noticias
    ORDER BY fecha DESC
");
$noticias = $stmt->fetchAll();
?>

<?php 
$pageTitle = "Noticias médicas";
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
        <h1 class="admin-title">Noticias</h1>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="admin-section">
        <div class="container">

            <div class="contenedor-noticias">

                <?php foreach ($noticias as $n): ?>
                    <div class="card tarjeta-noticia">

                        <?php if (!empty($n['imagen'])): ?>
                            <img src="uploads/<?= $n['imagen'] ?>" alt="<?= $n['titulo'] ?>">
                        <?php endif; ?>

                        <div class="contenido-noticia">
                            <h3><?= $n['titulo'] ?></h3>
                            <p class="fecha"><?= date("d/m/Y", strtotime($n['fecha'])) ?></p>
                            <p><?= substr($n['texto'], 0, 150) ?>...</p>
                            <a href="noticia-detalles.php?id=<?= $n['idNoticia'] ?>" class="btn btn-primario tarjeta-blog-btn">Leer más</a>
                        </div>

                    </div>
                <?php endforeach; ?>

            </div>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
