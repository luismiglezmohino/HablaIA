# =============================================================================
# HablaIA Backend - Production Dockerfile
# =============================================================================
# PHP 8.4 FPM optimizado para produccion (sin Symfony CLI, sin dev deps)

FROM php:8.4-fpm-alpine

LABEL maintainer="HablaIA"
LABEL description="Backend API Symfony 7 - Production"

# -----------------------------------------------------------------------------
# Dependencias del sistema (solo runtime, sin git/bash/curl)
# -----------------------------------------------------------------------------
RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    zip \
    unzip

# -----------------------------------------------------------------------------
# Extensiones PHP
# -----------------------------------------------------------------------------
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    opcache \
    zip

# -----------------------------------------------------------------------------
# Opcache produccion
# -----------------------------------------------------------------------------
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache-prod.ini \
    && echo "opcache.enable_cli=0" >> /usr/local/etc/php/conf.d/opcache-prod.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache-prod.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache-prod.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache-prod.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache-prod.ini

# -----------------------------------------------------------------------------
# PHP produccion
# -----------------------------------------------------------------------------
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/production.ini \
    && echo "upload_max_filesize=10M" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "post_max_size=10M" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "expose_php=Off" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "display_errors=Off" >> /usr/local/etc/php/conf.d/production.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/production.ini

# -----------------------------------------------------------------------------
# Composer
# -----------------------------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar composer files primero (layer caching)
COPY backend/composer.json backend/composer.lock ./

# Instalar solo dependencias de produccion
RUN composer install --no-dev --optimize-autoloader --no-scripts --prefer-dist

# Copiar codigo de la aplicacion
COPY backend/ .

# Post-install scripts (cache:clear, assets:install)
RUN APP_ENV=prod APP_SECRET=build-placeholder composer run-script post-install-cmd --no-interaction 2>/dev/null || true

# Calentar cache de Symfony
RUN APP_ENV=prod APP_SECRET=build-placeholder php bin/console cache:warmup --no-debug 2>/dev/null || true

# Permisos correctos
RUN chown -R www-data:www-data var/ public/

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
