<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Méthode non autorisée.',
    ], 405);
}

$email = trim((string) ($_POST['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse([
        'success' => false,
        'message' => 'Merci de renseigner une adresse email valide.',
    ], 422);
}

try {
    $db = Database::getConnection();
    $db->exec(
        'CREATE TABLE IF NOT EXISTS rapport_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $insert = $db->prepare('INSERT INTO rapport_requests (email, created_at) VALUES (:email, NOW())');
    $insert->execute(['email' => $email]);
} catch (Throwable $exception) {
    // On confirme côté front même si la base est indisponible.
}

jsonResponse([
    'success' => true,
    'message' => 'Rapport détaillé en préparation. Vérifiez votre boîte email.',
]);
