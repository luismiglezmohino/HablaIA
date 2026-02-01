---
name: docker
description: Docker with multi-stage builds, security best practices and Docker Compose
license: MIT
compatibility: opencode
metadata:
  type: infrastructure
  category: containerization
---

# SKILL: Docker

## 🛠 Tech Stack
- **Orquestación:** Docker Compose
- **Base Images:** Usar imágenes oficiales y minimalistas (e.g., `php:8.2-fpm-alpine`).

## ⚡ Arquitectura & Logs
1.  **Multi-Stage Builds:** Siempre usar builds multi-etapa para mantener las imágenes de producción limpias y pequeñas.
2.  **Non-Root User:** Ejecutar los contenedores con un usuario sin privilegios.
3.  **Logs:** Enviar logs a `stdout`/`stderr` para que Docker los gestione.

## ✅ Patrones (Snippets Reales)
### A. Multi-Stage Dockerfile para Symfony
```dockerfile
# Stage 1: Build
FROM composer:2 as vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

# Stage 2: Production Image
FROM php:8.2-fpm-alpine

COPY --from=vendor /app/vendor/ /app/vendor/
COPY . /app

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app
USER app

WORKDIR /app
```
### B. Docker Compose para el Stack
```yaml
version: '3.8'

services:
  php:
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - .:/app

  nginx:
    image: nginx:1.25-alpine
    ports:
      - "8080:80"
    volumes:
      - .:/app
      - ./nginx.conf:/etc/nginx/conf.d/default.conf

  db:
    image: postgres:15-alpine
    environment:
      POSTGRES_DB: main
      POSTGRES_USER: user
      POSTGRES_PASSWORD: password
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data/

volumes:
  postgres_data:
```