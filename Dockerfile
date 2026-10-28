# Build stage
FROM composer:2.7 AS vendor
WORKDIR /app

# Install required PHP extensions for Composer to resolve dependencies
RUN apk add --no-cache icu-dev libzip-dev bzip2-dev libsodium-dev \
    && docker-php-ext-install intl zip bz2 sodium

COPY database/ database/
COPY composer.json composer.lock ./
# ... (extension install lines) ...

# Force Git to use HTTPS instead of SSH for GitHub
RUN git config --global url."https://github.com/".insteadOf "git@github.com:"

COPY database/ database/
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# Production stage
# ...
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# Production stage
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache nginx supervisor curl icu-dev libzip-dev bzip2-dev libsodium-dev \
    && docker-php-ext-install pdo pdo_mysql bcmath opcache intl zip bz2 sodium

# Set working directory
WORKDIR /var/www/html

# Copy application code and vendor from the builder stage
COPY . .
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

# Copy Nginx and Supervisor configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set correct permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80 for the web server
EXPOSE 80

# Start Supervisor to manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]