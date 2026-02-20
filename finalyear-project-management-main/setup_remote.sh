#!/bin/bash
set -e

echo "Starting Setup..."
sudo apt install -y rsync

# 1. Database Setup
echo "Setting up Database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS project_management;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'laravel_user'@'localhost' IDENTIFIED BY 'MinhTho2024';"
sudo mysql -e "ALTER USER 'laravel_user'@'localhost' IDENTIFIED BY 'MinhTho2024';"
sudo mysql -e "GRANT ALL PRIVILEGES ON project_management.* TO 'laravel_user'@'localhost';"
sudo mysql -e "CREATE USER IF NOT EXISTS 'laravel_user'@'127.0.0.1' IDENTIFIED BY 'MinhTho2024';"
sudo mysql -e "ALTER USER 'laravel_user'@'127.0.0.1' IDENTIFIED BY 'MinhTho2024';"
sudo mysql -e "GRANT ALL PRIVILEGES ON project_management.* TO 'laravel_user'@'127.0.0.1';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo "DEBUG: Testing MySQL Connection (127.0.0.1)..."
mysql -u laravel_user -pMinhTho2024 -h 127.0.0.1 -e "SELECT 'Connection OK' as result;" || echo "Connection Failed 127.0.0.1"

echo "DEBUG: Testing MySQL Connection (localhost)..."
mysql -u laravel_user -pMinhTho2024 -h localhost -e "SELECT 'Connection OK' as result;" || echo "Connection Failed localhost"

cd /var/www/html/project-management

# Handle Nested Directory (using rsync to merge/overwrite)
if [ -d "finalyear-project-management-main" ]; then
    echo "Detected nested directory. Moving files up..."
    rsync -a finalyear-project-management-main/ .
    rm -rf finalyear-project-management-main
    echo "Files moved."
fi

# 3. Permissions
echo "Setting Permissions..."
sudo chown -R ubuntu:www-data .
sudo chmod -R 775 storage bootstrap/cache

# 4. Environment
echo "Configuring Environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    sed -i 's/DB_DATABASE=laravel/DB_DATABASE=project_management/' .env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=laravel_user/' .env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=MinhTho2024/' .env
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/' .env
    sed -i 's|APP_URL=http://localhost|APP_URL=http://18.142.184.196|' .env
    # Force generate key
    php artisan key:generate --force
fi

echo "Clearing stale config cache..."
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php

# 5. Install Dependencies (Unlimited Memory)
echo "Installing Composer Dependencies..."
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_MEMORY_LIMIT=-1
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 6. Build Assets (Remote Build)
echo "Building Frontend Assets..."
npm install
npm run build

# 7. Artisan Commands
echo "Running Artisan Commands..."
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php
php artisan optimize:clear
echo "DEBUG: Dumping DB Config..."
php artisan tinker --execute="dump(config('database.connections.mysql'))"
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan shield:setup --yes || true
php artisan shield:install --yes || true

# 8. Nginx Config
echo "Configuring Nginx..."
sudo tee /etc/nginx/sites-available/project-management <<EOF
server {
    listen 80;
    server_name 18.142.184.196;
    root /var/www/html/project-management/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

sudo ln -sf /etc/nginx/sites-available/project-management /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx

echo "Setup Complete!"
