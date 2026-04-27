<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLogged() {
    return isset($_SESSION['idUser']);
}

function isAdmin() {
    return isLogged() && $_SESSION['rol'] === 'admin';
}

