#!/usr/bin/env bash
# Tests para los endpoints de categorías
# Uso: ./docs/api-tests/categories.sh [BASE_URL]

BASE_URL="${1:-http://localhost:8080}"

echo "=== Category Tests ==="
echo "Base URL: $BASE_URL"
echo ""

echo "--- GET /api/categories ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/categories" | head -30
echo ""

echo "--- GET /api/categories/{id} (primer resultado) ---"
FIRST_ID=$(curl -s -H "Accept: application/json" "$BASE_URL/api/categories" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data[0]['id'] if data else '')" 2>/dev/null)
if [ -n "$FIRST_ID" ]; then
  curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/categories/$FIRST_ID"
else
  echo "No categories found, skipping"
fi
echo ""

echo "--- GET /api/categories/{invalid-uuid} (debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/categories/invalid-uuid"
echo ""

echo "--- GET /api/categories/{uuid-no-existe} (debe devolver 404) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/categories/00000000-0000-4000-8000-000000000099"
echo ""

