#!/bin/bash
# =============================================================================
# HablaIA - Initial Let's Encrypt certificate setup
# =============================================================================
# Run this ONCE on the server after DNS propagation.
# Usage: sudo bash init-letsencrypt.sh your-email@example.com
#
# Prerequisites:
#   - DNS A records for damevozya.es and www.damevozya.es pointing to this server
#   - Docker and docker compose installed
#   - Port 80 accessible from the internet

set -euo pipefail

DOMAIN="damevozya.es"
EMAIL="${1:-}"
COMPOSE_FILE="/opt/hablaia/docker-compose.prod.yml"

if [ -z "$EMAIL" ]; then
    echo "Usage: sudo bash init-letsencrypt.sh your-email@example.com"
    exit 1
fi

echo "=== Setting up Let's Encrypt for $DOMAIN ==="

# 1. Create certbot volumes
echo "--- Creating volumes ---"
docker volume create hablaia_certbot_etc 2>/dev/null || true
docker volume create hablaia_certbot_var 2>/dev/null || true

# 2. Stop nginx to free port 80
echo "--- Stopping nginx ---"
cd /opt/hablaia
docker compose -f "$COMPOSE_FILE" stop nginx 2>/dev/null || true

# 3. Request certificate in standalone mode (certbot runs its own server on port 80)
echo "--- Requesting Let's Encrypt certificate ---"
docker run --rm -p 80:80 \
    -v hablaia_certbot_etc:/etc/letsencrypt \
    -v hablaia_certbot_var:/var/www/certbot \
    certbot/certbot certonly \
        --standalone \
        --email "$EMAIL" \
        --agree-tos \
        --no-eff-email \
        -d "$DOMAIN" \
        -d "www.$DOMAIN"

# 4. Start all services with HTTPS
echo "--- Starting services with HTTPS ---"
docker compose -f "$COMPOSE_FILE" up -d

echo "=== Done! https://$DOMAIN should now be live ==="
