<?php
/**
 * Authentication and Role-Based Access Control (RBAC) Guard
 * Group 17 - AI Resume Analyzer
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'] ?? 'student',
        'department' => $_SESSION['user_department'] ?? ''
    ];
}

function requireLogin($redirect = '../login.php') {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Please log in to access this page.';
        header("Location: $redirect");
        exit();
    }
}

function requireRole($allowedRoles, $redirect = '../index.php') {
    requireLogin();
    $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    if (!in_array($_SESSION['user_role'], $roles)) {
        $_SESSION['flash_error'] = 'Unauthorized access: You do not have permission to view that resource.';
        header("Location: $redirect");
        exit();
    }
}

function flashMessage($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function displayFlash() {
    $types = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
    foreach ($types as $key => $bsClass) {
        $sessionKey = 'flash_' . $key;
        if (isset($_SESSION[$sessionKey])) {
            echo '<div class="alert alert-' . $bsClass . ' alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($_SESSION[$sessionKey]);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION[$sessionKey]);
        }
    }
}
