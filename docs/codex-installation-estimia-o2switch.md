# Codex d'installation / modification — EstimIA (o2switch cPanel)

Ce guide standardise l'installation d'**EstimIA** sur un hébergement mutualisé o2switch (PHP 8.1+, MySQL 5.7+) pour éviter les erreurs récurrentes (500, permissions, config corrompue).

---

## 1) Checklist Pré-Installation

- [ ] Vérifier la version PHP (`php -v` ≥ 8.1).
- [ ] Vérifier les extensions PHP requises (`pdo`, `mysqli`, `mbstring`, `gd`, `json`).
- [ ] Vérifier que le dossier cible existe (`/home/sc2tasq5564/sites/nantes/`).
- [ ] Vérifier les permissions de base (dossiers 755, fichiers 644).
- [ ] Vérifier le propriétaire attendu (`sc2tasq5564:sc2tasq5564`).
- [ ] Désactiver temporairement OPcache dans cPanel pendant l'installation/mise à jour.
- [ ] Sauvegarder l'existant (fichiers + base MySQL).

### Commandes utiles

```bash
php -v
php -m | grep -E 'pdo|mysqli|mbstring|gd|json'
find /home/sc2tasq5564/sites/nantes -maxdepth 2 -type d | head
find /home/sc2tasq5564/sites/nantes -maxdepth 2 -type f | head
apache2ctl -M | grep rewrite   # Peut être indisponible en mutualisé
```

> Si `apache2ctl` est indisponible en mutualisé, vérifiez `mod_rewrite` via cPanel > Apache Handlers / informations serveur, ou testez une règle Rewrite simple dans `.htaccess`.

---

## 2) Script Bash d'initialisation

Script fourni : `scripts/estimIA_o2switch_init.sh`

### Ce qu'il fait

1. Vérifie PHP 8.1+.
2. Vérifie les extensions requises.
3. Applique les permissions standard : dossiers `755`, fichiers `644`.
4. Marque certains dossiers applicatifs en écriture (`775`) s'ils existent (`storage`, `cache`, `logs`, `uploads`).
5. Tente un `chown` vers `sc2tasq5564:sc2tasq5564`.
6. Déploie les templates (`.htaccess`, `config.php`) s'ils sont absents.
7. Crée `installed.lock` (valeur `pending`) s'il manque.
8. Vérifie la syntaxe de `install/index.php` via `php -l` pour éviter les erreurs 500 dues aux accolades.

### Utilisation

```bash
cd /home/sc2tasq5564/sites/nantes
bash scripts/estimIA_o2switch_init.sh /home/sc2tasq5564/sites/nantes
```

Variables facultatives :

```bash
APP_USER=sc2tasq5564 APP_GROUP=sc2tasq5564 PHP_BIN=php bash scripts/estimIA_o2switch_init.sh
```

---

## 3) Modèles de fichiers (templates)

### a) `.htaccess`

Fichier modèle : `templates/install/.htaccess.example`

Objectifs :
- Forcer un front-controller (`index.php`) pour les routes.
- Bloquer l'accès aux fichiers sensibles (`config.php`, `.env`, `installed.lock`, etc.).
- Poser des en-têtes sécurité minimaux.

### b) `config.php`

Fichier modèle : `templates/install/config.php.example`

Bonnes pratiques :
- Format `return [ ... ];` strict et versionnable.
- `debug=false` en production.
- Identifiants DB explicites, encodage `utf8mb4`.
- Ne jamais laisser le fichier vide.

### c) `installed.lock`

Fichier modèle : `templates/install/installed.lock.example`

Rôle :
- Marquer l'état d'installation.
- Empêcher la réexécution intempestive du wizard install.

Recommandation : après installation réussie, remplacer `pending` par des métadonnées (`installed_at`, `status=ok`, `version`).

---

## 4) Procédure Post-Installation

1. **Finaliser `config.php`** : renseigner URL, DB, timezone.
2. **Tester la syntaxe PHP** :
   ```bash
   php -l install/index.php
   php -l config.php
   ```
3. **Activer le verrou** : vérifier `installed.lock` présent avec statut final.
4. **Réactiver OPcache** (si désactivé), puis vider les caches applicatifs.
5. **Retirer/limiter l'accès au dossier `install/`** en production.
6. **Sauvegarder l'état stable** : archive fichiers + dump SQL.

Exemple sauvegarde :

```bash
tar -czf backup-estimia-$(date +%F).tar.gz /home/sc2tasq5564/sites/nantes
mysqldump -u USER -p DATABASE > backup-estimia-$(date +%F).sql
```

---

## 5) Guide de dépannage

### Erreur 500 (Internal Server Error)

Causes fréquentes :
- Accolade non fermée / erreur de syntaxe PHP dans `install/index.php`.
- `config.php` vide/corrompu.
- Directive `.htaccess` non supportée.

Diagnostic rapide :

```bash
php -l install/index.php
php -l config.php
tail -n 100 ~/logs/error_log
```

Actions :
- Corriger la syntaxe et redéployer.
- Repartir d'un template `config.php.example` propre.
- Commenter temporairement les blocs `.htaccess` avancés pour isoler.

### Erreur 403 (Forbidden)

Causes :
- Droits trop stricts ou mauvais propriétaire.
- `Require all denied` appliqué trop largement.

Correction :

```bash
find /home/sc2tasq5564/sites/nantes -type d -exec chmod 755 {} +
find /home/sc2tasq5564/sites/nantes -type f -exec chmod 644 {} +
chown -R sc2tasq5564:sc2tasq5564 /home/sc2tasq5564/sites/nantes
```

### Erreur 404 (Not Found)

Causes :
- Rewrite non appliqué.
- Mauvais DocumentRoot (cPanel).
- `index.php` absent / mauvais emplacement.

Vérifications :
- Confirmer que `index.php` est au bon niveau.
- Vérifier `.htaccess` et support Rewrite.
- Vérifier le chemin du domaine dans cPanel (Document Root).

---

## Rappels anti-régression

- Toujours exécuter `php -l` sur les fichiers modifiés avant mise en ligne.
- Toujours garder un `config.php` non vide et valide.
- Toujours créer/mettre à jour `installed.lock` en fin d'installation.
- Toujours appliquer les permissions standard après upload FTP.
