<?php

function subirImagenSegura($campo, &$errores)
{
    // Si no se ha subido ninguna imagen REAL
    if (
        !isset($_FILES[$campo]) ||
        $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE ||
        $_FILES[$campo]['size'] === 0 ||
        empty($_FILES[$campo]['name'])
    ) {
        return ''; // No hay imagen nueva
    }

    $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
    $nombreOriginal = basename($_FILES[$campo]['name']);
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    if (!in_array($extension, $permitidos)) {
        $errores[] = "Formato no permitido. Solo JPG, PNG o WEBP.";
        return '';
    }

    if ($_FILES[$campo]['size'] > 5 * 1024 * 1024) {
        $errores[] = "La imagen no puede superar los 5MB.";
        return '';
    }

    if (!is_dir("uploads")) {
        mkdir("uploads", 0755, true);
    }

    // Nombre único SIEMPRE
    $nombreSeguro = uniqid('img_') . '.' . $extension;
    $destino = "uploads/" . $nombreSeguro;

    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destino)) {
        $errores[] = "Error al subir la imagen.";
        return '';
    }

    return $nombreSeguro;
}
