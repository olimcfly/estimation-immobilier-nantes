#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="${1:-/home/sc2tasq5564/sites/nantes}"
APP_USER="${APP_USER:-sc2tasq5564}"
APP_GROUP="${APP_GROUP:-sc2tasq5564}"
PHP_BIN="${PHP_BIN:-php}"

REQUIRED_EXTENSIONS=(pdo mysqli mbstring gd json)
WRITABLE_DIRS=(storage cache logs uploads)

log() { printf '[INFO] %s\n' "$*"; }
warn() { printf '[WARN] %s\n' "$*"; }
fail() { printf '[ERROR] %s\n' "$*"; exit 1; }

[ -d "$APP_ROOT" ] || fail "Le dossier APP_ROOT n'existe pas: $APP_ROOT"

log "1) Vérification version PHP >= 8.1"
PHP_VERSION="$($PHP_BIN -r 'echo PHP_VERSION;' 2>/dev/null || true)"
[ -n "$PHP_VERSION" ] || fail "Impossible d'exécuter PHP via: $PHP_BIN"

if ! "$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.1.0", ">=") ? 0 : 1);'; then
  fail "Version PHP insuffisante ($PHP_VERSION). 8.1+ requis."
fi
log "PHP OK ($PHP_VERSION)"

log "2) Vérification des extensions PHP requises"
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
  if "$PHP_BIN" -r "exit(extension_loaded('$ext') ? 0 : 1);"; then
    log "Extension OK: $ext"
  else
    fail "Extension manquante: $ext"
  fi
done

log "3) Application des permissions standard"
find "$APP_ROOT" -type d -exec chmod 755 {} +
find "$APP_ROOT" -type f -exec chmod 644 {} +

for dir in "${WRITABLE_DIRS[@]}"; do
  if [ -d "$APP_ROOT/$dir" ]; then
    chmod 775 "$APP_ROOT/$dir"
    log "Dossier writable ajusté: $dir (775)"
  fi
done

if command -v chown >/dev/null 2>&1; then
  chown -R "$APP_USER:$APP_GROUP" "$APP_ROOT" || warn "chown impossible (droits limités). À faire via cPanel File Manager."
fi

log "4) Installation des templates si absents"
if [ -f "$APP_ROOT/templates/install/.htaccess.example" ] && [ ! -f "$APP_ROOT/.htaccess" ]; then
  cp "$APP_ROOT/templates/install/.htaccess.example" "$APP_ROOT/.htaccess"
  log ".htaccess créé depuis template"
fi

if [ -f "$APP_ROOT/templates/install/config.php.example" ] && [ ! -f "$APP_ROOT/config.php" ]; then
  cp "$APP_ROOT/templates/install/config.php.example" "$APP_ROOT/config.php"
  chmod 640 "$APP_ROOT/config.php"
  log "config.php créé depuis template (pensez à compléter les valeurs)"
fi

if [ ! -f "$APP_ROOT/installed.lock" ]; then
  printf 'pending\n' > "$APP_ROOT/installed.lock"
  chmod 644 "$APP_ROOT/installed.lock"
  log "installed.lock initialisé à 'pending'"
fi

log "5) Vérification syntaxe de l'installateur"
if [ -f "$APP_ROOT/install/index.php" ]; then
  "$PHP_BIN" -l "$APP_ROOT/install/index.php"
fi

log "Terminé. Vérifiez OPcache dans cPanel (désactivation temporaire recommandée pendant l'installation)."
