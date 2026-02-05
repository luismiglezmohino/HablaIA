#!/usr/bin/env bash
# Tests para los endpoints de health check
# Uso: ./docs/api-tests/health.sh [BASE_URL]

BASE_URL="${1:-http://localhost:8080}"

echo "=== Health Check Tests ==="
echo "Base URL: $BASE_URL"
echo ""

echo "--- GET /api/health ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/health"
echo ""

echo "--- GET /api/health/live ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/health/live"
echo ""

echo "--- GET /api/health/ready ---"
curl -s -H "Accept: application/json" -w "\nHTTP %{http_code}\n" "$BASE_URL/api/health/ready"
echo ""
