#!/bin/bash
set -e

# Railway dynamically injects $PORT (e.g. 7432, 8080, 80).
PORT="${PORT:-80}"
echo "==> Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf
grep -q '^ServerName' /etc/apache2/apache2.conf || echo 'ServerName localhost' >> /etc/apache2/apache2.conf

echo "==> Ensuring storage and cache directories exist..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

# --- Required settings -------------------------------------------------------
# Accept Railway's MySQL connection URL if the individual DB_* variables are not set.
if [ -z "$DB_HOST" ] && [ -z "$DATABASE_URL" ] && [ -n "$MYSQL_URL" ]; then
    export DATABASE_URL="$MYSQL_URL"
fi

if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    export APP_KEY
    echo "!! ============================================================"
    echo "!! APP_KEY is not set. Using a temporary key so the site can start."
    echo "!! Logins will reset on every restart until you set it:"
    echo "!!   railway variables --set \"APP_KEY=$APP_KEY\""
    echo "!! ============================================================"
fi

DB_CONFIGURED=0
if { [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "localhost" ]; } || [ -n "$DATABASE_URL" ]; then
    DB_CONFIGURED=1
else
    echo "!! ============================================================"
    echo "!! No database configured (DB_HOST is empty)."
    echo "!! On this app service, set DB_HOST, DB_PORT, DB_DATABASE,"
    echo "!! DB_USERNAME and DB_PASSWORD to \${{MySQL.MYSQLHOST}} etc."
    echo "!! See RAILWAY_DEPLOYMENT_GUIDE.md. The site will show errors until then."
    echo "!! ============================================================"
fi
# ----------------------------------------------------------------------------

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

if [ "$DB_CONFIGURED" = "1" ]; then
    echo "==> Waiting for the database to accept connections..."
    for i in $(seq 1 30); do
        if php -r '
            $url = getenv("DATABASE_URL");
            if ($url) { $u = parse_url($url); $h=$u["host"]; $p=$u["port"] ?? 3306; $d=ltrim($u["path"] ?? "", "/"); $user=urldecode($u["user"] ?? ""); $pw=urldecode($u["pass"] ?? ""); }
            else { $h=getenv("DB_HOST"); $p=getenv("DB_PORT") ?: 3306; $d=getenv("DB_DATABASE"); $user=getenv("DB_USERNAME"); $pw=getenv("DB_PASSWORD"); }
            try { new PDO("mysql:host=$h;port=$p;dbname=$d", $user, $pw, [PDO::ATTR_TIMEOUT => 3]); exit(0); } catch (Throwable $e) { exit(1); }
        ' 2>/dev/null; then
            echo "==> Database is ready."
            break
        fi
        echo "    ...not ready yet ($i/30)"
        sleep 2
    done

    echo "==> Running database migrations..."
    php artisan migrate --force || echo "==> Warning: Migration failed. Continuing startup..."

    echo "==> Applying admin login from ADMIN_EMAIL / ADMIN_PASSWORD (if set)..."
    php artisan admin:ensure || echo "==> Warning: could not set admin login."
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
