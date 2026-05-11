<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/imagenes.php';

// Solo admins pueden entrar
if (!isLogged() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

$errores = [];
$exito = '';
$idUser = $_SESSION['idUser']; // autor de la noticia

// Crear carpeta uploads si no existe
if (!is_dir("uploads")) {
    mkdir("uploads", 0755, true);
}


// Crear noticia
if (isset($_POST['crear_noticia'])) {

    $titulo = trim($_POST['titulo']);
    $texto = trim($_POST['texto']);
    $fecha = date('Y-m-d');

    if ($titulo === '' || $texto === '') {
        $errores[] = "El título y el texto son obligatorios.";
    }

    // Subida segura
    $imagen = subirImagenSegura('imagen', $errores);

    if (empty($errores)) {
        $stmt = $pdo->prepare("
            INSERT INTO noticias (idUser, titulo, imagen, texto, fecha)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$idUser, $titulo, $imagen, $texto, $fecha]);

        $exito = "Noticia creada correctamente.";
    }
}

// Borrar noticia
if (isset($_GET['borrar'])) {

    $idNoticia = $_GET['borrar'];

    // Borrar imagen asociada
    $stmt = $pdo->prepare("SELECT imagen FROM noticias WHERE idNoticia=?");
    $stmt->execute([$idNoticia]);
    $img = $stmt->fetchColumn();

    if ($img && file_exists("uploads/" . $img)) {
        unlink("uploads/" . $img);
    }

    $pdo->prepare("DELETE FROM noticias WHERE idNoticia=?")->execute([$idNoticia]);

    $exito = "Noticia eliminada.";
}

// Cargar noticia para edición
$noticiaEditar = null;

if (isset($_GET['editar'])) {
    $idEditar = $_GET['editar'];

    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE idNoticia=?");
    $stmt->execute([$idEditar]);
    $noticiaEditar = $stmt->fetch();
}

// Guardar cambios de edición
if (isset($_POST['guardar_cambios'])) {

    $idNoticia = $_POST['idNoticia'];
    $titulo = trim($_POST['titulo']);
    $texto = trim($_POST['texto']);

    // Obtener imagen actual
    $stmt = $pdo->prepare("SELECT imagen FROM noticias WHERE idNoticia=?");
    $stmt->execute([$idNoticia]);
    $imagenActual = $stmt->fetchColumn();

    // Subida segura
    $imagenNueva = subirImagenSegura('imagen', $errores);

    // Si no se subió nueva imagen, mantener la anterior
    if ($imagenNueva === '') {
        $imagenNueva = $imagenActual;
    } else {
        // Borrar imagen anterior
        if ($imagenActual && file_exists("uploads/" . $imagenActual)) {
            unlink("uploads/" . $imagenActual);
        }
    }

    // Actualizar noticia
    $stmt = $pdo->prepare("
        UPDATE noticias
        SET titulo=?, texto=?, imagen=?
        WHERE idNoticia=?
    ");
    $stmt->execute([$titulo, $texto, $imagenNueva, $idNoticia]);

    $exito = "Noticia actualizada correctamente.";
    $noticiaEditar = null;
}

// Obtener todas las noticias
$stmt = $pdo->query("
    SELECT n.*, ud.nombre, ud.apellidos
    FROM noticias n
    JOIN users_data ud ON n.idUser = ud.idUser
    ORDER BY fecha DESC
");
$noticias = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/Trabajo_Final_Php/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Trabajo_Final_Php/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Trabajo_Final_Php/favicon-16x16.png">
    <link rel="manifest" href="/Trabajo_Final_Php/site.webmanifest">
    <link rel="icon" href="/Trabajo_Final_Php/favicon.ico">
    <title>Administrar Noticias</title>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <h1>Panel de administración de noticias</h1>

    <?php if (!empty($errores)): ?>
        <div class="msg-error">
            <?php foreach ($errores as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="msg-exito">
            <p><?= htmlspecialchars($exito) ?></p>
        </div>
    <?php endif; ?>


    <h2 class="seccion-titulo">Crear nueva noticia</h2>

    <form method="POST" enctype="multipart/form-data" class="admin-form">

        <input type="hidden" name="crear_noticia" value="1">

        <label>Título:</label>
        <input type="text" name="titulo" required>

        <label>Imagen:</label>
        <input type="file" name="imagen" accept="image/*">

        <label>Texto:</label>
        <textarea name="texto" required></textarea>

        <button type="submit">Publicar noticia</button>
    </form>

    <?php if ($noticiaEditar): ?>

    <h2 class="seccion-titulo">Editar noticia</h2>

    <form method="POST" enctype="multipart/form-data" class="admin-form-edit">

        <input type="hidden" name="guardar_cambios" value="1">
        <input type="hidden" name="idNoticia" value="<?= $noticiaEditar['idNoticia'] ?>">

        <label>Título:</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($noticiaEditar['titulo']) ?>" required>

        <label>Imagen actual:</label>
        <?php if (!empty($noticiaEditar['imagen'])): ?>
            <img src="uploads/<?= htmlspecialchars($noticiaEditar['imagen']) ?>" width="200">
        <?php else: ?>
            <p>No hay imagen asociada.</p>
        <?php endif; ?>

        <label>Subir nueva imagen (opcional):</label>
        <input type="file" name="imagen" accept="image/*">

        <label>Texto:</label>
        <textarea name="texto" rows="5" required><?= htmlspecialchars($noticiaEditar['texto']) ?></textarea>

        <button type="submit">Guardar cambios</button>
    </form>

    <?php endif; ?>

    <h2 class="seccion-titulo">Noticias publicadas</h2>

    <?php foreach ($noticias as $n): ?>
        <div class="noticia-card">

            <h3><?= htmlspecialchars($n['titulo']) ?></h3>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($n['fecha']) ?></p>
            <p><strong>Autor:</strong> <?= htmlspecialchars($n['nombre'] . " " . $n['apellidos']) ?></p>

            <?php if (!empty($n['imagen'])): ?>
                <img src="uploads/<?= htmlspecialchars($n['imagen']) ?>">
            <?php endif; ?>

            <p><?= nl2br(htmlspecialchars($n['texto'])) ?></p>

            <a href="noticias-administracion.php?borrar=<?= $n['idNoticia'] ?>" onclick="return confirm('¿Eliminar esta noticia?')">Eliminar</a>
            |
            <a href="noticias-administracion.php?editar=<?= $n['idNoticia'] ?>">Editar</a>

        </div>
    <?php endforeach; ?>

    <footer>
        <p>© 2026 Clínica SaludPlus — Todos los derechos reservados</p>
    </footer>

</body>
</html>
