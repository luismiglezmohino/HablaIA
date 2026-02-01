# =============================================================================
# HablaIA Backend - Dockerfile
# =============================================================================
# PHP 8.4 con extensiones para Symfony 7

FROM php:8.4-fpm-alpine

# Metadatos
LABEL maintainer="HablaIA"
LABEL description="Backend API Symfony 7 para HablaIA"

# -----------------------------------------------------------------------------
# Dependencias del sistema
# -----------------------------------------------------------------------------
RUN apk add --no-cache \
    # PostgreSQL
    postgresql-dev \
    # Intl
    icu-dev \
    # Zip
    libzip-dev \
    zip \
    unzip \
    # Git (para Composer)
    git \
    # Symfony CLI
    bash \
    curl

# -----------------------------------------------------------------------------
# Extensiones PHP
# -----------------------------------------------------------------------------
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    opcache \
    zip

# -----------------------------------------------------------------------------
# Composer
# -----------------------------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------------------------
# Symfony CLI
# -----------------------------------------------------------------------------
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# -----------------------------------------------------------------------------
# Configuración PHP para desarrollo
# -----------------------------------------------------------------------------
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory-limit.ini \
    && echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/uploads.ini

# -----------------------------------------------------------------------------
# Directorio de trabajo
# -----------------------------------------------------------------------------
WORKDIR /var/www

# -----------------------------------------------------------------------------
# Usuario no-root para seguridad
# -----------------------------------------------------------------------------
RUN addgroup -g 1000 appgroup \
    && adduser -u 1000 -G appgroup -s /bin/sh -D appuser

USER appuser

# -----------------------------------------------------------------------------
# Puerto expuesto
# -----------------------------------------------------------------------------
EXPOSE 8000

# -----------------------------------------------------------------------------
# Comando por defecto
# -----------------------------------------------------------------------------
CMD ["symfony", "server:start", "--no-tls", "--port=8000"]
