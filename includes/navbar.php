<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <div class="navbar-container">

        <div class="navbar-logo">
            <a href="index.php">SaludPlus</a>
        </div>

        <ul class="navbar-links">

            <li><a href="index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">Inicio</a></li>
            <li><a href="noticias.php" class="<?= $current === 'noticias.php' ? 'active' : '' ?>">Noticias</a></li>

            <?php if (!isset($_SESSION['idUser'])): ?>

                <!-- VISITANTE -->
                <li><a href="login.php" class="<?= $current === 'login.php' ? 'active' : '' ?>">Login</a></li>
                <li><a href="registro.php" class="<?= $current === 'registro.php' ? 'active' : '' ?>">Registro</a></li>

            <?php else: ?>

                <?php if ($_SESSION['rol'] === 'admin'): ?>

                    <!-- ADMIN -->
                    <li><a href="usuarios-administracion.php" class="<?= $current === 'usuarios-administracion.php' ? 'active' : '' ?>">Usuarios admin</a></li>
                    <li><a href="citas-administracion.php" class="<?= $current === 'citas-administracion.php' ? 'active' : '' ?>">Citas admin</a></li>
                    <li><a href="noticias-administracion.php" class="<?= $current === 'noticias-administracion.php' ? 'active' : '' ?>">Noticias admin</a></li>

                <?php else: ?>

                    <!-- USUARIO NORMAL -->
                    <li><a href="perfil.php" class="<?= $current === 'perfil.php' ? 'active' : '' ?>">Mi perfil</a></li>
                    <li><a href="citaciones.php" class="<?= $current === 'citaciones.php' ? 'active' : '' ?>">Mis citas</a></li>

                <?php endif; ?>

                <!-- COMÚN A TODOS LOS LOGUEADOS -->
                <li><a href="logout.php">Cerrar sesión</a></li>

            <?php endif; ?>

        </ul>

    </div>
</nav>
