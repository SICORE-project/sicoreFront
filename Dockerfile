# --- Stage 1: Build Frontend Assets (Vite) ---
FROM node:20-alpine AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build


# --- Stage 2: Main Application Runtime ---
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html


# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS


# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache


# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis


# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer


# Copy application
COPY . .


# Copy Vite production assets
COPY --from=frontend-builder /app/public/build ./public/build


# Install Laravel dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-interaction \
    --optimize-autoloader \
    --no-dev


# Laravel permissions
RUN chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache \
    && chmod -R 775 \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache


EXPOSE 9000

CMD ["php-fpm"]