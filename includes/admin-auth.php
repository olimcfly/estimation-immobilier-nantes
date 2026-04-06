<?php

require_once __DIR__ . '/security.php';

initSecureSession();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

// Rafraîchir le statut en ligne
if (function_exists('refreshOnlineStatuses')) {
    refreshOnlineStatuses();
}

// Suivre l'activité (ex: accès à une page)
if (function_exists('trackUserActivity')) {
    trackUserActivity((int) $_SESSION['admin_id'], 'Accès à ' . ($_SERVER['REQUEST_URI'] ?? '')); 
}
