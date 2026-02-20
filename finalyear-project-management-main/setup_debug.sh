#!/bin/bash
set -e

echo "Starting Debug Setup..."
cd /var/www/html/project-management

# Ensure .env exists and is configured correctly
if [ -f .env ]; then
    echo "Updating existing .env..."
    sed -i 's/^DB_DATABASE=.*/DB_DATABASE=project_management/' .env
    sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=root/' .env
    sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
    sed -i 's|^APP_URL=.*|APP_URL=http://18.142.184.196|' .env
else
    echo "Creating .env from example..."
    cp .env.example .env
    sed -i 's/^DB_DATABASE=.*/DB_DATABASE=project_management/' .env
    sed -i 's/^DB_USERNAME=.*/DB_USERNAME=laravel_user/' .env
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=MinhTho2024/' .env
    sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
    sed -i 's|^APP_URL=.*|APP_URL=http://18.142.184.196|' .env
    php artisan key:generate --force
fi

echo "DEBUG: Checking .env DB_HOST and DB_PASSWORD..."
grep "DB_HOST" .env
grep "DB_PASSWORD" .env

echo "DEBUG: Testing PHP PDO Connection..."
php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=project_management', 'laravel_user', 'MinhTho2024');
    echo 'PHP PDO Connection (127.0.0.1): SUCCESS' . PHP_EOL;
} catch (PDOException \$e) {
    echo 'PHP PDO Connection (127.0.0.1): FAILED - ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

echo "Clearing stale config cache..."
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php

echo "Installing Composer Dependencies..."
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_MEMORY_LIMIT=-1
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

echo "Building Frontend..."
npm install
npm run build

echo "Migrating..."
php artisan migrate --force

echo "Deployment Debug Script Complete!"
