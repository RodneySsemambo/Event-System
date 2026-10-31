# Build stage
FROM composer:2.7 AS vendor
WORKDIR /app

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl zip gd pdo_pgsql pdo_mysql bcmath opcache exif pcntl

COPY database/ database/
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# Production stage
FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor curl
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl zip gd pdo_pgsql pdo_mysql bcmath opcache exif pcntl

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
