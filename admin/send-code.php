<?php

declare(strict_types=1);

require_once __DIR__ . '/auth-utils.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

$pendingAdminId = (int) ($_SESSION['admin_pending_id'] ?? 0);
$pendingEmail = (string) ($_SESSION['admin_pending_email'] ?? '');

if ($pendingAdminId <= 0 || $pendingEmail === '') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expirée, reconnectez-vous.']);
    exit;
}

$lastSentAt = (int) ($_SESSION['admin_code_sent_at'] ?? 0);
if ($lastSentAt > 0 && (time() - $lastSentAt) < 30) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Patientez 30 secondes avant de renvoyer un code.']);
    exit;
}

try {
    $db = Database::getConnection();
    adminEnsureTables($db);

    $stmt = $db->prepare('SELECT id, prenom, nom, email FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $pendingAdminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($admin)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Administrateur introuvable.']);
        exit;
    }

    $result = adminGenerateAndSendCode($db, $admin);
    $_SESSION['admin_code_sent_at'] = time();

    if (!$result['sent']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Code généré mais email non envoyé.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Nouveau code envoyé.']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur pendant l\'envoi du code.']);
}
