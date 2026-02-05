#!/usr/bin/env bash
# Ejecuta todos los tests de la API secuencialmente
# Uso: ./docs/api-tests/run-all.sh [BASE_URL]

BASE_URL="${1:-http://localhost:8080}"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "============================================"
echo "  HablaIA - API Tests"
echo "  Base URL: $BASE_URL"
echo "============================================"
echo ""

for script in "$SCRIPT_DIR"/health.sh "$SCRIPT_DIR"/categories.sh "$SCRIPT_DIR"/pictograms.sh "$SCRIPT_DIR"/phrases.sh; do
  echo ""
  echo "--------------------------------------------"
  bash "$script" "$BASE_URL"
  echo "--------------------------------------------"
done

echo ""
echo "============================================"
echo "  Todos los tests ejecutados"
echo "============================================"
