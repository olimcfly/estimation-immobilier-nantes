<?php
/** @var string $prenom */
/** @var string $nom */
/** @var string $siteName */
/** @var string $cityName */
/** @var string $baseUrl */
?>
<!doctype html>
<html lang="fr">
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 12px;background:#eff6ff;">
    <tr>
        <td align="center">
            <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.08);">
                <tr>
                    <td style="background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:24px 28px;color:#fff;">
                        <h1 style="margin:0;font-size:22px;">Installation réussie ✅</h1>
                        <p style="margin:6px 0 0;opacity:.9;font-size:14px;"><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($cityName, ENT_QUOTES, 'UTF-8'); ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 12px;font-size:16px;">Bonjour <?php echo htmlspecialchars(trim($prenom . ' ' . $nom), ENT_QUOTES, 'UTF-8'); ?>,</p>
                        <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#334155;">Votre environnement EstimIA est prêt. Vous pouvez maintenant accéder à l'administration.</p>
                        <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#334155;">La connexion admin utilise un code à 6 chiffres envoyé par email à chaque tentative de connexion.</p>
                        <a href="<?php echo htmlspecialchars(rtrim($baseUrl, '/') . '/admin/login.php', ENT_QUOTES, 'UTF-8'); ?>" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;">Ouvrir l'administration</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
