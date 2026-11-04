#!/bin/sh
# No `set -e` here — we want the container to start even if an artisan
# command fails (e.g. migrations already done, seeders re-running, etc.)

echo "Creating storage directories..."
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

echo "Linking storage..."
php /var/www/html/artisan storage:link || echo "storage:link skipped"

echo "Running migrations..."
php /var/www/html/artisan migrate --force || echo "migrations skipped"

echo "Caching config..."
php /var/www/html/artisan config:cache || echo "config:cache skipped"
php /var/www/html/artisan route:cache || echo "route:cache skipped"
php /var/www/html/artisan view:cache || echo "view:cache skipped"

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf