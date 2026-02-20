#!/bin/bash
set -e

echo "Updating MySQL Root Password to 'root'..."

# Connect as root with current password (MinhTho2024) and change to 'root'
mysql -u root -pMinhTho2024 -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root'; FLUSH PRIVILEGES;"

echo "Verifying Root Access with new password..."
mysql -u root -proot -e "SELECT 'Root Login OK' as result;"

echo "Updating .env..."
cd /var/www/html/project-management
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=root/' .env

echo "Clearing Caches..."
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

echo "Restarting Services..."
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm

echo "Done! Root password is now: root"
