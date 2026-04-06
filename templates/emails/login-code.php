<?php
/** @var string $prenom */
/** @var string $code */
/** @var int $expiresMinutes */
/** @var string $siteName */
/** @var string $cityName */
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
                        <h1 style="margin:0;font-size:22px;"><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p style="margin:6px 0 0;opacity:.9;font-size:14px;">Connexion administration · <?php echo htmlspecialchars($cityName, ENT_QUOTES, 'UTF-8'); ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <p style="margin:0 0 12px;font-size:16px;">Bonjour <?php echo htmlspecialchars($prenom !== '' ? $prenom : 'administrateur', ENT_QUOTES, 'UTF-8'); ?>,</p>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">Voici votre code de connexion à l'espace admin.</p>
                        <div style="margin:18px 0;padding:16px 12px;border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;text-align:center;">
                            <div style="font-size:34px;font-weight:700;letter-spacing:10px;color:#1e3a8a;"><?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <p style="margin:0;color:#475569;font-size:14px;">Ce code expire dans <?php echo (int) $expiresMinutes; ?> minutes.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
