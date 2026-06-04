#!/bin/bash
set -e

echo "Waiting for PostgreSQL to be ready..."
until php -r "new PDO('pgsql:host=pg-master;port=5432;dbname=zita_rutas', 'postgres', 'postgres');" 2>/dev/null; do
    sleep 2
done
echo "PostgreSQL is ready."

php artisan migrate --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
