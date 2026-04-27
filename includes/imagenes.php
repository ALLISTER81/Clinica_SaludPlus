<?php

function subirImagenSegura($campo, &$errores)
{
    // Si no se ha subido ninguna imagen
    if (
        !isset($_FILES[$campo]) ||
        $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE ||
        empty($_FILES[$campo]['name'])
    ) {
        return '';
    }

    $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
    $nombreOriginal = basename($_FILES[$campo]['name']);
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    // Validar extensión
    if (!in_array($extension, $permitidos)) {
        $errores[] = "Formato no permitido. Solo JPG, PNG o WEBP.";
        return '';
    }

    // Validar tamaño (máx 5MB)
    if ($_FILES[$campo]['size'] > 5 * 1024 * 1024) {
        $errores[] = "La imagen no puede superar los 5MB.";
        return '';
    }

    // Asegurar carpeta uploads
    if (!is_dir("uploads")) {
        mkdir("uploads", 0755, true);
    }

    // Ruta destino
    $destino = "uploads/" . $nombreOriginal;

    // Si ya existe un archivo con ese nombre, lo sobrescribimos
    if (file_exists($destino)) {
        unlink($destino);
    }

    // Mover archivo sin renombrar
    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destino)) {
        $errores[] = "Error al subir la imagen.";
        return '';
    }

    return $nombreOriginal;
}
