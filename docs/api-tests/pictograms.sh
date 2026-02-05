#!/usr/bin/env bash
# Tests para los endpoints de pictogramas
# Uso: ./docs/api-tests/pictograms.sh [BASE_URL]

BASE_URL="${1:-http://localhost:8080}"

echo "=== Pictogram Tests ==="
echo "Base URL: $BASE_URL"
echo ""

echo "--- GET /api/pictograms ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms" | head -30
echo ""

echo "--- GET /api/pictograms?categoryId={id} ---"
CATEGORY_ID=$(curl -s -H "Accept: application/json" "$BASE_URL/api/categories" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data[0]['id'] if data else '')" 2>/dev/null)
if [ -n "$CATEGORY_ID" ]; then
  curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms?categoryId=$CATEGORY_ID" | head -30
else
  echo "No categories found, skipping"
fi
echo ""

echo "--- GET /api/pictograms/search?q=comer ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms/search?q=comer"
echo ""

echo "--- GET /api/pictograms/search (sin query, debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms/search"
echo ""

echo "--- GET /api/pictograms/search?q=a (query corta, debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms/search?q=a"
echo ""

echo "--- GET /api/pictograms/{id} (primer resultado) ---"
FIRST_ID=$(curl -s -H "Accept: application/json" "$BASE_URL/api/pictograms" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data[0]['id'] if data else '')" 2>/dev/null)
if [ -n "$FIRST_ID" ]; then
  curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms/$FIRST_ID"
else
  echo "No pictograms found, skipping"
fi
echo ""

echo "--- GET /api/pictograms/{uuid-no-existe} (debe devolver 404) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms/00000000-0000-4000-8000-000000000099"
echo ""

echo "--- GET /api/pictograms/{invalid-uuid} (debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms/invalid-uuid"
echo ""

echo "--- GET /api/pictograms?categoryId=invalid (debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms?categoryId=invalid-uuid"
echo ""

LONG_QUERY=$(python3 -c "print('a' * 101)")
echo "--- GET /api/pictograms/search?q=101chars (debe devolver 400) ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/pictograms/search?q=$LONG_QUERY"
echo ""
