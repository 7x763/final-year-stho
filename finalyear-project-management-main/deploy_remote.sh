#!/bin/bash
set -e

echo "Starting Deployment..."

# 0. Install PHP 8.4 if not already installed
echo "Checking PHP version..."
CURRENT_PHP=$(php -r 'echo PHP_VERSION;' 2>/dev/null | cut -d'.' -f1,2 || echo "0.0")
if [[ "$CURRENT_PHP" != "8.4" ]]; then
    echo "Installing PHP 8.4..."
    sudo apt-get update
    sudo apt-get install -y software-properties-common
    sudo add-apt-repository -y ppa:ondrej/php
    sudo apt-get update
    sudo apt-get install -y php8.4 php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml \
        php8.4-bcmath php8.4-curl php8.4-gd php8.4-intl php8.4-zip php8.4-pdo

    # Install Composer if not installed
    if ! command -v composer &> /dev/null; then
        echo "Installing Composer..."
        curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
    fi

    # Make php8.4 the default
    sudo update-alternatives --set php /usr/bin/php8.4

    # Stop old php-fpm if running, start php8.4-fpm
    sudo systemctl stop php8.3-fpm 2>/dev/null || true
    sudo systemctl disable php8.3-fpm 2>/dev/null || true
    sudo systemctl enable php8.4-fpm
    sudo systemctl start php8.4-fpm
    echo "PHP 8.4 installed successfully."
else
    echo "PHP 8.4 already installed."
    sudo systemctl start php8.4-fpm 2>/dev/null || true
fi

# 1. Database Setup
echo "Setting up Database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS project_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'laravel_user'@'localhost' IDENTIFIED BY 'MinhTho@2024';"
sudo mysql -e "GRANT ALL PRIVILEGES ON project_management.* TO 'laravel_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# 2. Clone Repository
echo "Cleaning up old directory..."
sudo rm -rf /var/www/html/project-management

echo "Cloning Repository..."
sudo mkdir -p /var/www/html
cd /var/www/html
sudo git clone https://github.com/7x763/final-year-stho.git project-management
cd project-management

# 3. Permissions
echo "Setting Permissions..."
sudo chown -R ubuntu:www-data .
sudo chmod -R 775 storage bootstrap/cache

# 4. Environment
echo "Configuring Environment..."
cp .env.example .env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=project_management/' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=laravel_user/' .env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=MinhTho@2024/' .env
sed -i 's|APP_URL=http://localhost|APP_URL=http://18.142.184.196|' .env
sed -i 's/^GEMINI_API_KEY=.*/GEMINI_API_KEY=AIzaSyBRsRbo4U_hnIfnxSIvRBjlSdsQ4QuHXcw/' .env
php artisan key:generate

# 5. Install Dependencies
echo "Installing Composer Dependencies..."
composer install --no-dev --optimize-autoloader

# 6. Build Assets (Remote Build)
echo "Building Frontend Assets..."
npm install
npm run build

# 7. Artisan Commands
echo "Running Artisan Commands..."
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
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
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

echo "Deployment Complete!"
