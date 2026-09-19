#!/bin/bash
set -e

# Railway dynamically injects $PORT (e.g. 7432, 8080, 80).
PORT="${PORT:-80}"
echo "==> Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

echo "==> Ensuring storage and cache directories exist..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

echo "==> Clearing cached configuration (ensures fresh env vars are used)..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

if [ ! -L "public/storage" ] && [ ! -L "storage/app/public" ]; then
    echo "==> Creating storage symlink..."
    php artisan storage:link || true
fi

echo "==> Running package discovery..."
php artisan package:discover --ansi || true

# Run database migrations if DB is configured
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
    echo "==> Database detected ($DB_HOST). Running database migrations..."
    php artisan migrate --force || echo "==> Warning: Migration failed. Continuing startup..."
fi

echo "==> Final permissions fix (in case artisan created any root-owned files)..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

echo "==> Forcing mpm_prefork as the only active MPM..."
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
a2enmod mpm_prefork 2>/dev/null || true

echo "==> Starting Apache Web Server on port ${PORT}..."
exec apache2-foreground
