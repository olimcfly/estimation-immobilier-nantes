<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function jsonResponse(array $payload): void
{
    http_response_code(200);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function normalize(string $value): string
{
    return trim($value);
}

function computeBantScore(string $projet, string $decisionnaire, string $delai, string $budget, string $methodeVente): int
{
    $scoreProjet = match ($projet) {
        'Vendre mon bien' => 30,
        'Acheter un bien' => 25,
        "Investir dans l'immobilier" => 20,
        default => 0,
    };

    $scoreDecisionnaire = match ($decisionnaire) {
        'Oui, seul(e)' => 25,
        'Oui, en couple / à plusieurs' => 20,
        "Non, je me renseigne pour quelqu'un" => 5,
        default => 0,
    };

    $scoreDelai = match ($delai) {
        'Urgent (moins de 3 mois)' => 25,
        'Dans les 6 mois' => 20,
        "Dans l'année" => 10,
        'Pas de délai précis' => 5,
        default => 0,
    };

    $scoreBudget = match ($budget) {
        'Plus de 800 000 €', '500 000 € - 800 000 €' => 20,
        '300 000 € - 500 000 €' => 15,
        '150 000 € - 300 000 €' => 10,
        'Moins de 150 000 €' => 5,
        default => 0,
    };

    $scoreMethodeVente = match ($methodeVente) {
        'Agence' => 5,
        'Coach' => 10,
        'Je ne sais pas' => 3,
        'Seul' => 0,
        default => 0,
    };

    return min(100, $scoreProjet + $scoreDecisionnaire + $scoreDelai + $scoreBudget + $scoreMethodeVente);
}

function sendAdminNotification(array $lead, int $score): bool
{
    $to = defined('ADMIN_EMAIL') ? (string) ADMIN_EMAIL : '';
    if ($to === '') {
        return false;
    }

    $fromEmail = defined('MAIL_FROM') ? (string) MAIL_FROM : 'no-reply@localhost';
    $fromName = defined('MAIL_FROM_NAME') ? (string) MAIL_FROM_NAME : (defined('SITE_NAME') ? (string) SITE_NAME : 'EstimIA');
    $subject = 'Nouveau lead BANT - ' . ($lead['prenom'] ?? 'Contact');

    $html = '<h2>Nouveau contact estimation</h2>'
        . '<p><strong>Prénom:</strong> ' . htmlspecialchars((string) $lead['prenom'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Téléphone:</strong> ' . htmlspecialchars((string) $lead['telephone'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Email:</strong> ' . htmlspecialchars((string) $lead['email'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<hr>'
        . '<p><strong>Projet:</strong> ' . htmlspecialchars((string) $lead['projet'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Méthode de vente:</strong> ' . htmlspecialchars((string) $lead['methode_vente'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Source:</strong> ' . htmlspecialchars((string) $lead['source_site'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Décisionnaire:</strong> ' . htmlspecialchars((string) $lead['decisionnaire'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Budget:</strong> ' . htmlspecialchars((string) $lead['budget_bant'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Délai:</strong> ' . htmlspecialchars((string) $lead['delai'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Raison:</strong> ' . htmlspecialchars((string) $lead['raison'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Score BANT:</strong> ' . $score . '/100</p>'
        . '<hr>'
        . '<p><strong>Type bien:</strong> ' . htmlspecialchars((string) $lead['type_bien'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Ville:</strong> ' . htmlspecialchars((string) $lead['ville'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Surface:</strong> ' . htmlspecialchars((string) $lead['surface_tranche'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><strong>Estimation:</strong> ' . htmlspecialchars((string) $lead['estimation_basse'], ENT_QUOTES, 'UTF-8')
        . ' € - ' . htmlspecialchars((string) $lead['estimation_haute'], ENT_QUOTES, 'UTF-8') . ' €</p>';

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
    ];

    return mail($to, $subject, $html, implode("\r\n", $headers));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Méthode non autorisée.',
    ]);
}

try {
    $lead = [
        'prenom' => normalize((string) ($_POST['prenom'] ?? '')),
        'telephone' => normalize((string) ($_POST['telephone'] ?? '')),
        'email' => normalize((string) ($_POST['email'] ?? '')),
        'projet' => normalize((string) ($_POST['projet'] ?? '')),
        'methode_vente' => normalize((string) ($_POST['methode_vente'] ?? '')),
        'source_site' => normalize((string) ($_POST['source_site'] ?? '')),
        'decisionnaire' => normalize((string) ($_POST['decisionnaire'] ?? '')),
        'budget_bant' => normalize((string) ($_POST['budget_bant'] ?? '')),
        'delai' => normalize((string) ($_POST['delai'] ?? '')),
        'raison' => normalize((string) ($_POST['raison'] ?? '')),
        'type_bien' => normalize((string) ($_POST['type_bien'] ?? '')),
        'ville' => normalize((string) ($_POST['ville'] ?? '')),
        'surface_tranche' => normalize((string) ($_POST['surface_tranche'] ?? '')),
        'estimation_basse' => normalize((string) ($_POST['estimation_basse'] ?? '')),
        'estimation_haute' => normalize((string) ($_POST['estimation_haute'] ?? '')),
    ];

    if ($lead['prenom'] === '' || $lead['telephone'] === '' || $lead['email'] === '') {
        jsonResponse([
            'success' => false,
            'message' => 'Merci de renseigner prénom, téléphone et email.',
        ]);
    }

    if (!filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse([
            'success' => false,
            'message' => 'Adresse email invalide.',
        ]);
    }

    $requiredBant = ['projet', 'methode_vente', 'source_site', 'decisionnaire', 'budget_bant', 'delai', 'raison'];
    foreach ($requiredBant as $field) {
        if ($lead[$field] === '') {
            jsonResponse([
                'success' => false,
                'message' => 'Merci de compléter la qualification avant de continuer.',
            ]);
        }
    }

    $scoreBant = computeBantScore($lead['projet'], $lead['decisionnaire'], $lead['delai'], $lead['budget_bant'], $lead['methode_vente']);

    $db = Database::getConnection();

    $db->exec(
        'CREATE TABLE IF NOT EXISTS leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            prenom VARCHAR(100),
            telephone VARCHAR(30),
            email VARCHAR(255),
            projet VARCHAR(50),
            methode_vente VARCHAR(100),
            source_site VARCHAR(100),
            decisionnaire VARCHAR(50),
            budget_bant VARCHAR(50),
            delai VARCHAR(50),
            raison VARCHAR(100),
            type_bien VARCHAR(80),
            ville VARCHAR(120),
            surface_tranche VARCHAR(50),
            estimation_basse VARCHAR(50),
            estimation_haute VARCHAR(50),
            score_bant INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $schemaUpdates = [
        'ALTER TABLE leads ADD COLUMN projet VARCHAR(50)',
        'ALTER TABLE leads ADD COLUMN methode_vente VARCHAR(100)',
        'ALTER TABLE leads ADD COLUMN source_site VARCHAR(100)',
        'ALTER TABLE leads ADD COLUMN decisionnaire VARCHAR(50)',
        'ALTER TABLE leads ADD COLUMN budget_bant VARCHAR(50)',
        'ALTER TABLE leads ADD COLUMN delai VARCHAR(50)',
        'ALTER TABLE leads ADD COLUMN raison VARCHAR(100)',
        'ALTER TABLE leads ADD COLUMN score_bant INT DEFAULT 0',
        'ALTER TABLE leads ADD COLUMN type_bien VARCHAR(80)',
        'ALTER TABLE leads ADD COLUMN ville VARCHAR(120)',
        'ALTER TABLE leads ADD COLUMN surface_tranche VARCHAR(50)',
        'ALTER TABLE leads ADD COLUMN estimation_basse VARCHAR(50)',
        'ALTER TABLE leads ADD COLUMN estimation_haute VARCHAR(50)',
    ];

    foreach ($schemaUpdates as $sql) {
        try {
            $db->exec($sql);
        } catch (Throwable $exception) {
            // Colonne déjà existante ou structure différente: on continue sans bloquer.
        }
    }

    $insert = $db->prepare(
        'INSERT INTO leads
        (prenom, telephone, email, projet, methode_vente, source_site, decisionnaire, budget_bant, delai, raison, type_bien, ville, surface_tranche, estimation_basse, estimation_haute, score_bant)
        VALUES
        (:prenom, :telephone, :email, :projet, :methode_vente, :source_site, :decisionnaire, :budget_bant, :delai, :raison, :type_bien, :ville, :surface_tranche, :estimation_basse, :estimation_haute, :score_bant)'
    );

    $insert->execute([
        'prenom' => $lead['prenom'],
        'telephone' => $lead['telephone'],
        'email' => $lead['email'],
        'projet' => $lead['projet'],
        'methode_vente' => $lead['methode_vente'],
        'source_site' => $lead['source_site'],
        'decisionnaire' => $lead['decisionnaire'],
        'budget_bant' => $lead['budget_bant'],
        'delai' => $lead['delai'],
        'raison' => $lead['raison'],
        'type_bien' => $lead['type_bien'],
        'ville' => $lead['ville'],
        'surface_tranche' => $lead['surface_tranche'],
        'estimation_basse' => $lead['estimation_basse'],
        'estimation_haute' => $lead['estimation_haute'],
        'score_bant' => $scoreBant,
    ]);

    sendAdminNotification($lead, $scoreBant);

    jsonResponse([
        'success' => true,
        'message' => 'Votre demande a bien été enregistrée. Un conseiller vous rappelle sous 24h.',
        'score_bant' => $scoreBant,
    ]);
} catch (Throwable $exception) {
    jsonResponse([
        'success' => false,
        'message' => 'Le service de rappel est temporairement indisponible. Merci de réessayer dans quelques instants.',
    ]);
}
