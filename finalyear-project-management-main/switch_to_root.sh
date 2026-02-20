#!/bin/bash
set -e

echo "Switching to Root User..."
cd /var/www/html/project-management

# 1. Update MySQL Root User
echo "Configuring MySQL Root User..."
# Set root password to MinhTho2024 and ensure native password plugin is used for compatibility
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'MinhTho2024';"
sudo mysql -e "FLUSH PRIVILEGES;"

# 2. Update .env
echo "Updating .env..."
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=MinhTho2024/' .env

# 3. Clear Caches
echo "Clearing Caches..."
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# 4. Restart Nginx/PHP (Optional but good practice)
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm

echo "Switched to Root User Successfully!"
