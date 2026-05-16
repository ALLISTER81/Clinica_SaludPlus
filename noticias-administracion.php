<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/imagenes.php';

// Solo admins
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

/* ============================================================
   CREAR NOTICIA
   ============================================================ */
if (isset($_POST['crear_noticia'])) {

    $titulo = trim($_POST['titulo']);
    $texto = trim($_POST['texto']);
    $fecha = date('Y-m-d');

    if ($titulo === '' || $texto === '') {
        $errores[] = "El título y el texto son obligatorios.";
    }

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

/* ============================================================
   BORRAR NOTICIA
   ============================================================ */
if (isset($_GET['borrar'])) {

    $idNoticia = $_GET['borrar'];

    $stmt = $pdo->prepare("SELECT imagen FROM noticias WHERE idNoticia=?");
    $stmt->execute([$idNoticia]);
    $img = $stmt->fetchColumn();

    if ($img && file_exists("uploads/" . $img)) {
        unlink("uploads/" . $img);
    }

    $pdo->prepare("DELETE FROM noticias WHERE idNoticia=?")->execute([$idNoticia]);

    $exito = "Noticia eliminada.";
}

/* ============================================================
   CARGAR NOTICIA PARA EDICIÓN
   ============================================================ */
$noticiaEditar = null;

if (isset($_GET['editar'])) {

    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE idNoticia=?");
    $stmt->execute([$_GET['editar']]);
    $noticiaEditar = $stmt->fetch();
}

/* ============================================================
   GUARDAR CAMBIOS DE EDICIÓN
   ============================================================ */
if (isset($_POST['guardar_cambios'])) {

    $idNoticia = $_POST['idNoticia'];
    $titulo = trim($_POST['titulo']);
    $texto = trim($_POST['texto']);

    $stmt = $pdo->prepare("SELECT imagen FROM noticias WHERE idNoticia=?");
    $stmt->execute([$idNoticia]);
    $imagenActual = $stmt->fetchColumn();

    $imagenNueva = subirImagenSegura('imagen', $errores);

    if ($imagenNueva === '') {
        $imagenNueva = $imagenActual;
    } else {
        if ($imagenActual && file_exists("uploads/" . $imagenActual)) {
            unlink("uploads/" . $imagenActual);
        }
    }

    $stmt = $pdo->prepare("
        UPDATE noticias
        SET titulo=?, texto=?, imagen=?
        WHERE idNoticia=?
    ");
    $stmt->execute([$titulo, $texto, $imagenNueva, $idNoticia]);

    $exito = "Noticia actualizada correctamente.";
    $noticiaEditar = null;
}

/* ============================================================
   OBTENER TODAS LAS NOTICIAS
   ============================================================ */
$stmt = $pdo->query("
    SELECT n.*, ud.nombre, ud.apellidos
    FROM noticias n
    JOIN users_data ud ON n.idUser = ud.idUser
    ORDER BY fecha DESC
");
$noticias = $stmt->fetchAll();
?>

<?php 
$pageTitle = "Administrar Noticias";
$isAdmin = true;
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
        <h1 class="admin-title">Panel de administración de noticias</h1>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <section class="admin-section">
        <div class="admin-container">        

            <!-- Mensajes -->
            <?php if (!empty($errores)): ?>
                <div class="mensaje-error">
                    <?php foreach ($errores as $e): ?>
                        <?= htmlspecialchars($e) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($exito): ?>
                <div class="mensaje-exito"><?= htmlspecialchars($exito) ?></div>
            <?php endif; ?>


            <!-- CREAR NOTICIA -->
            <h2>Crear nueva noticia</h2>

            <form method="POST" enctype="multipart/form-data" class="admin-form">

                <input type="hidden" name="crear_noticia" value="1">

                <label>Título:</label>
                <input type="text" name="titulo" required>

                <label>Imagen:</label>
                <input type="file" name="imagen" accept="image/*">

                <label>Texto:</label>
                <textarea name="texto" required></textarea>

                <button type="submit" class="btn btn-primario">Publicar noticia</button>
            </form>


            <!-- EDITAR NOTICIA -->
            <?php if ($noticiaEditar): ?>

                <h2>Editar noticia</h2>

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

                    <button type="submit" class="btn btn-primario">Guardar cambios</button>
                </form>

            <?php endif; ?>

        </div> <!-- cierre admin-container -->
    </section>


    <!-- LISTADO DE NOTICIAS -->
    <section class="admin-section">

        <h2 class="admin-subtitle">Noticias publicadas</h2>

        <div class="admin-table-wrapper">
            <table class="admin-table">

                <tr>
                    <th>Título</th>
                    <th>Fecha</th>
                    <th>Autor</th>
                    <th>Imagen</th>
                    <th class="acciones">Acciones</th>
                </tr>

                <?php foreach ($noticias as $n): ?>
                    <tr>
                        <td><?= htmlspecialchars($n['titulo']) ?></td>
                        <td><?= htmlspecialchars($n['fecha']) ?></td>
                        <td><?= htmlspecialchars($n['nombre'] . " " . $n['apellidos']) ?></td>

                        <td>
                            <?php if (!empty($n['imagen'])): ?>
                                <img src="uploads/<?= htmlspecialchars($n['imagen']) ?>" width="80">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td class="acciones">
                            <a href="noticias-administracion.php?editar=<?= $n['idNoticia'] ?>" class="btn-accion btn-editar">
                                Editar
                            </a>

                            <a href="noticias-administracion.php?borrar=<?= $n['idNoticia'] ?>"
                               class="btn-accion btn-borrar"
                               onclick="return confirm('¿Eliminar esta noticia?')">
                                Borrar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>
        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
