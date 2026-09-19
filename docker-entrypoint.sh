#!/bin/bash
set -e

# Railway dynamically injects $PORT (e.g. 7432, 8080, 80).
PORT="${PORT:-80}"
echo "==> Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure writable storage and cache directories
echo "==> Verifying storage and cache permissions..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create storage symlink if not already created
if [ ! -L "public/storage" ] && [ ! -L "storage/app/public" ]; then
    echo "==> Creating storage symlink..."
    php artisan storage:link || true
fi

# Clear any cached configuration from build stage
echo "==> Optimizing configuration cache..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "==> Running package discovery..."
php artisan package:discover --ansi || true

# Run database migrations if DB is configured
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
    echo "==> Database detected ($DB_HOST). Running database migrations..."
    php artisan migrate --force || echo "==> Warning: Migration failed. Continuing startup..."
fi

echo "==> DEBUG: Apache modules loaded at runtime:"
apache2ctl -M || true
echo "==> DEBUG: mods-enabled MPM files:"
ls -la /etc/apache2/mods-enabled/ | grep -i mpm || true

echo "==> Starting Apache Web Server on port ${PORT}..."
exec apache2-foreground
