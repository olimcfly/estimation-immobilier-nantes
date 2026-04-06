# Audit interface admin (préliminaire)

Date de l'audit: 2026-04-06 (UTC).

## Contexte
Le dépôt local ne contient pas le code source de l'admin (aucun fichier applicatif présent), donc les corrections directes sur `lead.php`, `webhooks.php` ou les templates ne peuvent pas être appliquées ici.

## Vérifications exécutées
- Requête HTTP vers:
  - `/admin/lead.php`
  - `/admin/webhooks.php`
  - `/admin/google_ads.php`

Résultat: le runner retourne `403 CONNECT tunnel failed` avant d'atteindre les pages (limitation réseau de l'environnement d'exécution), donc impossible de valider le HTML/PHP runtime depuis ce conteneur.

## Causes probables (pages blanches)
Sur PHP en production, une page blanche provient souvent de:
1. erreur fatale masquée (`display_errors=Off`),
2. include/require cassé,
3. variable non définie avec `TypeError` en PHP 8+,
4. sortie bloquée par `exit` prématuré,
5. erreur SQL non gérée.

## Correctifs à appliquer côté code (checklist)
1. **Activer logs d'erreurs PHP côté serveur** (pas en affichage direct utilisateur):
   - `display_errors=Off`
   - `log_errors=On`
   - `error_log=/var/log/php/admin-error.log`
2. **Envelopper chaque page admin critique** (`lead.php`, `webhooks.php`) avec bootstrap commun:
   - chargement session/auth,
   - autoload,
   - gestion exceptions.
3. **Corriger lien “estimation” inexistant**:
   - identifier la route cible réelle (`estimations.php` ou autre),
   - mettre à jour menu + redirections.
4. **Double sidebar Google Ads**:
   - conserver 1 sidebar globale admin,
   - convertir la sidebar interne Google Ads en onglets horizontaux ou ancre locale,
   - ou masquer la sidebar secondaire via classe dédiée (voir `docs/css/google_ads_sidebar_fix.css`).
5. **Ajouter un script de monitoring** pour détecter pages blanches et liens morts après déploiement (voir `scripts/admin_link_audit.sh`).

## Livrables ajoutés dans ce dépôt
- `scripts/admin_link_audit.sh`: audit HTTP des pages admin + détection réponse vide.
- `docs/css/google_ads_sidebar_fix.css`: correctif CSS minimal pour éviter l'effet de double sidebar.

