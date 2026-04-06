<?php
/**
 * Cron job : envoi des relances automatiques
 * À configurer dans cPanel O2Switch :
 * /usr/local/bin/php /home/cpanuser/public_html/estimia/cron/send-relances.php
 *
 * Fréquence : tous les jours à 10h
 * Cron : 0 10 * * *
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../classes/Mailer.php';

$db = Database::getConnection();
$mailer = new Mailer();

$stmt = $db->query("
    SELECT e.* FROM estimations e
    WHERE e.type_estimation = 'simple'
    AND e.rdv_pris = 0
    AND e.email_relance_j3 = 0
    AND e.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 4 DAY)
                         AND DATE_SUB(NOW(), INTERVAL 3 DAY)
    AND e.unsubscribed = 0
    AND e.email IS NOT NULL
    AND e.email != ''
    LIMIT 50
");

$leads = $stmt->fetchAll();
$sent = 0;

foreach ($leads as $lead) {
    $success = $mailer->send(
        $lead['email'],
        'Votre bien à ' . $lead['ville'] . ' : affinons l\'estimation',
        'relance-j3',
        [
            'prenom' => $lead['prenom'],
            'type_bien' => $lead['type_bien'],
            'adresse' => $lead['adresse'],
            'ville' => $lead['ville'],
            'prix_estime' => number_format((float) $lead['prix_estime'], 0, ',', ' '),
            'prix_bas_large' => number_format(((float) $lead['prix_estime']) * 0.85, 0, ',', ' '),
            'prix_haut_large' => number_format(((float) $lead['prix_estime']) * 1.15, 0, ',', ' '),
            'estimation_id' => $lead['id'],
            'recipient_email' => $lead['email'],
            'unsubscribe_token' => md5($lead['email'] . 'unsub_salt_' . $lead['id']),
        ]
    );

    if ($success) {
        $db->prepare('UPDATE estimations SET email_relance_j3 = 1 WHERE id = ?')
           ->execute([$lead['id']]);
        $sent++;
    }
}

file_put_contents(
    __DIR__ . '/../logs/cron.log',
    date('Y-m-d H:i:s') . " - Relance J3 : $sent/" . count($leads) . " envoyés\n",
    FILE_APPEND
);

echo 'Relance J3 : ' . $sent . ' emails envoyés sur ' . count($leads) . ' leads.';
