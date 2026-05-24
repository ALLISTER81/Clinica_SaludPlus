<?php
require_once 'includes/db.php';

$stmt = $pdo->query("
    SELECT n.*, u.nombre, u.apellidos
    FROM noticias n
    JOIN users_data u ON n.idUser = u.idUser
    ORDER BY fecha DESC, idNoticia DESC
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
    <h1 class="admin-title">Noticias</h1>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="noticias-section">
    <div class="container">

        <?php if (empty($noticias)): ?>
            <div class="citas-placeholder">
                <p>No hay noticias publicadas todavía.</p>
            </div>
        <?php else: ?>

            <div class="contenedor-noticias noticias-grid">

                <?php foreach ($noticias as $n): ?>
                    <div class="tarjeta-noticia-completa">

                        <?php if (!empty($n['imagen'])): ?>
                            <img src="uploads/<?= htmlspecialchars($n['imagen']) ?>" 
                                 alt="<?= htmlspecialchars($n['titulo']) ?>">
                        <?php endif; ?>

                        <div class="contenido-noticia">

                            <h3><?= htmlspecialchars($n['titulo']) ?></h3>

                            <p class="fecha">
                                Publicado el <?= date("d/m/Y", strtotime($n['fecha'])) ?>
                                por <?= htmlspecialchars($n['nombre'] . " " . $n['apellidos']) ?>
                            </p>

                            <p class="texto-noticia">
                                <?= nl2br(htmlspecialchars($n['texto'])) ?>
                            </p>

                        </div>

                    </div>
                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
