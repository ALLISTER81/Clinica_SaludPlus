<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Obtener últimas 3 noticias
$stmt = $pdo->query("
    SELECT titulo, imagen, texto, fecha
    FROM noticias
    ORDER BY fecha DESC
    LIMIT 3
");
$ultimasNoticias = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/hero.js"></script>
    <title>Clínica SaludPlus</title>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- ============================= -->
<!-- SECCIÓN HERO / PORTADA -->
<!-- ============================= -->

<div class="hero-wrapper">

    <div class="hero-texto">
        <h1>Bienvenido a SaludPlus</h1>
        <p>Tu bienestar es nuestra prioridad</p>
    </div>

    <div class="hero-slider">
        <img src="assets/img/fachada_clinica.png" class="slide active">
        <img src="assets/img/saludplus-logo-horizontal.png" class="slide">
        <img src="assets/img/recepcion_clinica.png" class="slide">
    </div>

</div>


<!-- ============================= -->
<!-- SECCIÓN SOBRE NOSOTROS -->
<!-- ============================= -->
<section style="padding:20px;">
    <h2>Sobre nosotros</h2>
    <p>
        En Clínica SaludPlus ofrecemos atención médica de calidad, con un equipo de profesionales
        especializados en medicina general, pediatría, cardiología y más.
    </p>
    <p>
        Nuestro objetivo es brindar un servicio cercano, humano y eficiente para mejorar tu bienestar.
    </p>
</section>

<hr>

<!-- ============================= -->
<!-- SECCIÓN SERVICIOS -->
<!-- ============================= -->
<section style="padding:20px;">
    <h2>Servicios médicos</h2>

    <ul>
        <li>✔ Consultas de medicina general</li>
        <li>✔ Especialidades médicas</li>
        <li>✔ Revisión y seguimiento de pacientes</li>
        <li>✔ Certificados médicos</li>
        <li>✔ Atención pediátrica</li>
    </ul>

    <br>

    <?php if (isLogged()): ?>
        <a href="citaciones.php">
            <button>Pedir cita médica</button>
        </a>
    <?php else: ?>
        <a href="registro.php">
            <button>Registrarse como paciente</button>
        </a>
    <?php endif; ?>
</section>

<hr>

<!-- ============================= -->
<!-- SECCIÓN ÚLTIMAS NOTICIAS -->
<!-- ============================= -->
<section class="noticias">
    <h2>Últimas noticias médicas</h2>

    <?php if (count($ultimasNoticias) === 0): ?>
        <p>No hay noticias publicadas todavía.</p>
    <?php else: ?>
        <div class="contenedor-noticias">
            <?php foreach ($ultimasNoticias as $n): ?>
                <div class="tarjeta-noticia">
                    <?php if (!empty($n['imagen'])): ?>
                        <img src="uploads/<?= $n['imagen'] ?>" alt="<?= $n['titulo'] ?>">
                    <?php endif; ?>

                    <div class="contenido-noticia">
                        <h3><?= $n['titulo'] ?></h3>
                        <p class="fecha"><?= date("d/m/Y", strtotime($n['fecha'])) ?></p>
                        <p><?= substr($n['texto'], 0, 150) ?>...</p>
                        <a href="noticias.php" class="boton">Leer más noticias</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


<hr>

<!-- ============================= -->
<!-- PIE DE PÁGINA -->
<!-- ============================= -->
<footer style="text-align:center; padding:20px; color:#555;">
    <p>© 2026 Clínica SaludPlus — Todos los derechos reservados</p>
</footer>



</body>
</html>
