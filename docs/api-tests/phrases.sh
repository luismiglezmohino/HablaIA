#!/usr/bin/env bash
# Tests para el endpoint de generación de frases
# Uso: ./docs/api-tests/phrases.sh [BASE_URL]

BASE_URL="${1:-http://localhost:8080}"

echo "=== Phrase Generation Tests ==="
echo "Base URL: $BASE_URL"
echo ""

# Obtener un pictogram ID real para los tests
PICTOGRAM_ID=$(curl -s -H "Accept: application/json" "$BASE_URL/api/pictograms" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data[0]['id'] if data else '')" 2>/dev/null)

echo "--- POST /api/phrases/generate (con pictograma real) ---"
if [ -n "$PICTOGRAM_ID" ]; then
  curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" -X POST "$BASE_URL/api/phrases/generate" \
    -H "Content-Type: application/json" \
    -d "{\"pictogramIds\": [\"$PICTOGRAM_ID\"]}"
else
  echo "No pictograms found, skipping"
fi
echo ""

echo "--- POST /api/phrases/generate (sin body, debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" -X POST "$BASE_URL/api/phrases/generate" \
  -H "Content-Type: application/json"
echo ""

echo "--- POST /api/phrases/generate (JSON invalido, debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" -X POST "$BASE_URL/api/phrases/generate" \
  -H "Content-Type: application/json" \
  -d "invalid json {"
echo ""

echo "--- POST /api/phrases/generate (campo faltante, debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" -X POST "$BASE_URL/api/phrases/generate" \
  -H "Content-Type: application/json" \
  -d '{"other": "field"}'
echo ""

echo "--- POST /api/phrases/generate (array vacio, debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" -X POST "$BASE_URL/api/phrases/generate" \
  -H "Content-Type: application/json" \
  -d '{"pictogramIds": []}'
echo ""

echo "--- POST /api/phrases/generate (UUID invalido, debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" -X POST "$BASE_URL/api/phrases/generate" \
  -H "Content-Type: application/json" \
  -d '{"pictogramIds": ["invalid-uuid"]}'
echo ""

echo "--- POST /api/phrases/generate (pictograma inexistente, debe devolver 404) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" -X POST "$BASE_URL/api/phrases/generate" \
  -H "Content-Type: application/json" \
  -d '{"pictogramIds": ["00000000-0000-4000-8000-000000000099"]}'
echo ""

