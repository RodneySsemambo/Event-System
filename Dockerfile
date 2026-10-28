# Build stage
FROM composer:2.7 AS vendor
WORKDIR /app

# Install the extension installer script, then install ALL required extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl zip gd pdo_pgsql pdo_mysql bcmath opcache exif pcntl

COPY database/ database/
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# Production stage
FROM php:8.3-fpm-alpine

# Install Nginx, Supervisor, and PHP extensions
RUN apk add --no-cache nginx supervisor curl
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl zip gd pdo_pgsql pdo_mysql bcmath opcache exif pcntl

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]