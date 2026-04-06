<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/leads/index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$status = (string) ($_POST['status'] ?? 'new');
$allowed = ['new', 'contacted', 'converted', 'lost'];

if ($id > 0 && in_array($status, $allowed, true)) {
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE leads SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);

        if ($status === 'new' && defined('ADMIN_NOTIFICATION_EMAIL') && ADMIN_NOTIFICATION_EMAIL !== '') {
            @mail((string) ADMIN_NOTIFICATION_EMAIL, 'Nouveau lead Google Ads', 'Un lead vient d\'être marqué comme nouveau dans l\'admin.');
        }
    } catch (Throwable $e) {
        error_log('Erreur update statut lead: ' . $e->getMessage());
    }
}

$back = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '/admin/leads/index.php';
header('Location: ' . $back);
exit;
