<?php
/**
 * Archivo de autenticación reutilizable
 * Este archivo verifica si el usuario ha iniciado sesión y tiene el rol correcto
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario ha iniciado sesión
 * @return bool True si el usuario ha iniciado sesión, false en caso contrario
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Verifica si el usuario tiene un rol específico
 * @param string $role El rol a verificar ('admin' o 'user')
 * @return bool True si el usuario tiene el rol especificado, false en caso contrario
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

/**
 * Redirige al usuario a la página de inicio de sesión si no ha iniciado sesión
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . getBaseUrl() . 'index.php');
        exit();
    }
}

/**
 * Redirige al usuario a la página de inicio de sesión si no tiene el rol especificado
 * @param string $role El rol requerido ('admin' o 'user')
 */
function requireRole($role) {
    if (!hasRole($role)) {
        header('Location: ' . getBaseUrl() . 'index.php');
        exit();
    }
}

/**
 * Obtiene la URL base del sitio
 * @return string La URL base
 */
function getBaseUrl() {
    return 'https://vaion.neositio.com/';
}

?>