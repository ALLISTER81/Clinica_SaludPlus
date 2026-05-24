<!-- META BÁSICOS -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- SEO -->
<meta name="description" content="Clínica SaludPlus — Atención médica integral, especialistas, noticias de salud y gestión de citas.">
<meta name="author" content="Clínica SaludPlus">

<!-- FAVICONS -->
<link rel="icon" href="favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
<link rel="manifest" href="site.webmanifest">

<!-- ESTILOS GLOBALES -->
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/public.css">
<link rel="stylesheet" href="assets/css/navbar.css">

<!-- ESTILOS FOOTER -->
<link rel="stylesheet" href="/assets/css/footer.css">

<!-- ESTILOS ADMIN (solo si aplica) -->
<?php if (isset($isAdmin) && $isAdmin): ?>
    <link rel="stylesheet" href="assets/css/admin.css">
<?php endif; ?>

<!-- TÍTULO DINÁMICO -->
<title><?= isset($pageTitle) ? $pageTitle . ' — SaludPlus' : 'Clínica SaludPlus' ?></title>
