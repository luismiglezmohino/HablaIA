# =============================================================================
# HablaIA Nginx - Production Dockerfile
# =============================================================================
# Stage 1: Build Vue.js frontend con Vite
# Stage 2: Nginx con frontend dist + pictogramas + config produccion

# --- Stage 1: Build frontend ---
FROM node:20-alpine AS frontend-build

ARG VITE_SENTRY_DSN
ENV VITE_SENTRY_DSN=$VITE_SENTRY_DSN

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --ignore-scripts

COPY frontend/ .
RUN npm run build

# --- Stage 2: Production nginx ---
FROM nginx:1.27-alpine

LABEL maintainer="HablaIA"
LABEL description="Nginx - frontend SPA + reverse proxy - Production"

RUN rm /etc/nginx/conf.d/default.conf

# Config nginx produccion
COPY docker/production/nginx.conf /etc/nginx/conf.d/default.conf

# Frontend built assets (SPA)
COPY --from=frontend-build /app/dist /usr/share/nginx/html

# Directorio para pictogramas (servidos via volumen compartido con backend)
RUN mkdir -p /var/www/public/pictograms

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -q --spider http://localhost/health || exit 1

CMD ["nginx", "-g", "daemon off;"]
