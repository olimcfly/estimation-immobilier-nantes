<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';

$email = trim((string) ($_GET['email'] ?? ''));
$token = trim((string) ($_GET['token'] ?? ''));

$isValid = false;
if ($email !== '' && $token !== '') {
    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT id FROM estimations WHERE email = ? ORDER BY id DESC');
    $stmt->execute([$email]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($ids as $id) {
        if (hash_equals(md5($email . 'unsub_salt_' . $id), $token)) {
            $isValid = true;
            break;
        }
    }

    if ($isValid) {
        $update = $db->prepare('UPDATE estimations SET unsubscribed = 1 WHERE email = ?');
        $update->execute([$email]);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Désinscription - <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body style="margin:0; font-family:Arial, sans-serif; background:#f4f4f5; color:#1f2937;">
    <div style="max-width:680px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08);">
        <div style="background:<?php echo htmlspecialchars(SITE_COLOR, ENT_QUOTES, 'UTF-8'); ?>; color:#fff; padding:24px 32px;">
            <h1 style="margin:0; font-size:24px;"><?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div style="padding:32px; line-height:1.7;">
            <?php if ($isValid): ?>
                <h2 style="margin-top:0;">✅ Désinscription confirmée</h2>
                <p>Vous ne recevrez plus d'emails de notre part.</p>
            <?php else: ?>
                <h2 style="margin-top:0;">Lien invalide</h2>
                <p>Le lien de désinscription est invalide ou expiré.</p>
            <?php endif; ?>
            <p><a href="<?php echo htmlspecialchars(SITE_URL, ENT_QUOTES, 'UTF-8'); ?>" style="color:<?php echo htmlspecialchars(SITE_COLOR, ENT_QUOTES, 'UTF-8'); ?>;">Retour au site</a></p>
        </div>
    </div>
</body>
</html>
