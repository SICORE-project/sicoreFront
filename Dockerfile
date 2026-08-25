# ==========================================================
# 1. INSTALLATION DES DEPENDANCES COMPOSER
# ==========================================================

FROM composer:2 AS vendor

WORKDIR /app

# Copier uniquement les fichiers Composer
COPY composer.json composer.lock* ./

# Installer les dépendances PHP
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

# Copier le reste du projet
COPY . .

# Régénérer l'autoload Laravel
RUN composer dump-autoload \
    --optimize \
    --no-dev


# ==========================================================
# 2. IMAGE DE PRODUCTION PHP-FPM + NGINX
# ==========================================================

FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# ----------------------------------------------------------
# Packages système + extensions PHP
# ----------------------------------------------------------

RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        intl \
        mbstring \
        pdo \
        pdo_mysql \
        zip \
        opcache \
    && rm -rf /var/cache/apk/*


# ----------------------------------------------------------
# Copier l'application
# ----------------------------------------------------------

COPY --from=vendor /app /var/www/html


# ----------------------------------------------------------
# Configuration Nginx
# ----------------------------------------------------------

COPY docker/nginx.conf /etc/nginx/http.d/default.conf


# ----------------------------------------------------------
# Configuration Supervisor
# ----------------------------------------------------------

COPY docker/supervisord.conf /etc/supervisord.conf


# ----------------------------------------------------------
# Permissions Laravel
# ----------------------------------------------------------

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache


# ----------------------------------------------------------
# Configuration PHP OPcache
# ----------------------------------------------------------

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=10000'; \
    } > /usr/local/etc/php/conf.d/opcache.ini


# ----------------------------------------------------------
# Port HTTP
# ----------------------------------------------------------

EXPOSE 8080


# ----------------------------------------------------------
# Démarrage
# ----------------------------------------------------------

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]