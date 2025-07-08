#!/bin/sh

# langsung lanjut tanpa pg_isready
echo "⏳ Starting Laravel setup..."

composer install
php artisan key:generate
php artisan migrate --force
php artisan config:cache

echo "🚀 Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
