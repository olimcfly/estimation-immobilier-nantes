#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-https://estimation-immobilier-bordeaux.fr/admin}"
PAGES=(
  "index.php"
  "estimations.php"
  "lead.php"
  "webhooks.php"
  "parametres.php"
  "google_ads.php"
  "exports.php"
)

echo "Audit admin: $BASE_URL"

tmp_headers="$(mktemp)"
tmp_body="$(mktemp)"
trap 'rm -f "$tmp_headers" "$tmp_body"' EXIT

for page in "${PAGES[@]}"; do
  url="$BASE_URL/$page"
  code="$(curl -sS -L -D "$tmp_headers" -o "$tmp_body" -w '%{http_code}' "$url" || true)"
  bytes="$(wc -c < "$tmp_body" | tr -d ' ')"
  ctype="$(awk -F': ' 'tolower($1)=="content-type"{print $2}' "$tmp_headers" | tail -1 | tr -d '\r')"

  if [[ "$code" == "000" ]]; then
    status="NETWORK_ERROR"
  elif [[ "$code" =~ ^2|3 ]]; then
    if [[ "$bytes" -lt 120 ]]; then
      status="WARN_BLANK_OR_TINY"
    else
      status="OK"
    fi
  else
    status="HTTP_$code"
  fi

  printf '%-18s %-6s %-10s %s\n' "$page" "$code" "$bytes" "$status"
  if [[ -n "$ctype" ]]; then
    echo "  content-type: $ctype"
  fi
done
