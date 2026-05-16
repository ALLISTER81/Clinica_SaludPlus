<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Obtener últimas 3 noticias
$stmt = $pdo->query("
    SELECT idNoticia, titulo, imagen, texto, fecha
    FROM noticias
    ORDER BY fecha DESC
    LIMIT 3
");
$ultimasNoticias = $stmt->fetchAll();
?>

<?php 
$pageTitle = "Inicio";
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

    <!-- HERO -->
    <section class="hero-section">

        <div class="hero-texto">
            <h1>En SaludPlus ofrecemos una atención integral</h1>
            <p>
                Basada en tecnología avanzada y un equipo médico altamente cualificado.
                Cada especialidad está diseñada para garantizar diagnósticos precisos,
                tratamientos eficaces y una experiencia cómoda para el paciente.
            </p>
        </div>

        <div class="hero-wrapper">
            <div class="hero-slider">
                <img src="assets/img/saludplus-logo-horizontal.png" class="hero-slide active">
                <img src="assets/img/fachada_clinica.png" class="hero-slide">
                <img src="assets/img/recepcion_clinica.png" class="hero-slide">
                <img src="assets/img/sala_espera.png" class="hero-slide">
            </div>
        </div>

    </section>

    <hr>  

    <!-- SERVICIOS -->
    <section class="servicios">

        <h2 class="titulo-servicios">Nuestros Servicios</h2>

        <p class="intro-servicios">
            Nuestros servicios médicos están pensados para ofrecerle atención rápida, precisa y de calidad.
        </p>

        <div class="servicios-grid">

            <ul class="lista-servicios">
                <li><strong>Medicina General</strong></li>
                <li><strong>Cardiología</strong></li>
                <li><strong>Radiología Digital</strong></li>
                <li><strong>Resonancia Magnética 3D</strong></li>
                <li><strong>Análisis Clínicos</strong></li>
            </ul>

            <ul class="lista-servicios">
                <li><strong>Pediatría</strong></li>
                <li><strong>Fisioterapia</strong></li>
                <li><strong>Oftalmología</strong></li>
                <li><strong>Podología</strong></li>
                <li><strong>Odontología</strong></li>
            </ul>

        </div>

        <div class="boton-cita-contenedor">
            <?php if (isLogged()): ?>
                <a href="citaciones.php" class="btn btn-primario">Pedir cita médica</a>
            <?php else: ?>
                <a href="registro.php" class="btn btn-primario">Registrarse como paciente</a>
            <?php endif; ?>
        </div>

    </section>

    <hr>

    <!-- NOTICIAS -->
    <section class="noticias seccion-noticias">

        <h2 class="titulo-noticias">Últimas noticias médicas</h2>

        <?php if (count($ultimasNoticias) === 0): ?>

            <p>No hay noticias publicadas todavía.</p>

        <?php else: ?>

            <div class="contenedor-noticias">

                <?php foreach ($ultimasNoticias as $n): ?>
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

        <?php endif; ?>

    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="scripts/hero.js"></script>
<script src="scripts/main.js"></script>

</body>
</html>
