#!/bin/sh
set -e

# Render/railway PORT deta hai; local me default 80
: "${PORT:=80}"
export PORT

# nginx config ko asli PORT ke sath generate karein
envsubst '${PORT}' < /var/www/nginx.conf > /etc/nginx/sites-available/default

# Laravel permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Migrations + admin seed (idempotent)
php artisan config:clear >/dev/null 2>&1 || true
php artisan migrate --force || true
php artisan db:seed --force || true

# Start nginx + php-fpm
service nginx start
exec php-fpm
