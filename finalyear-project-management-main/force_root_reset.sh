#!/bin/bash
set -e

echo "Stopping MySQL..."
sudo service mysql stop

echo "Starting MySQL in Safe Mode..."
# Start mysqld_safe in background without grant tables
sudo mysqld_safe --skip-grant-tables &
sleep 10

echo "Resetting Root Password..."
# Connect as root (no password needed now) and update user
sudo mysql -e "FLUSH PRIVILEGES; ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'MinhTho2024'; FLUSH PRIVILEGES;"

echo "Stopping Safe Mode MySQL..."
sudo pkill mysqld
sleep 5

echo "Starting Normal MySQL..."
sudo service mysql start

echo "Verifying Root Access..."
mysql -u root -pMinhTho2024 -e "SELECT 'Root Login OK';"

echo "Updating .env..."
cd /var/www/html/project-management
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=MinhTho2024/' .env

echo "Clearing Caches..."
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

echo "Done! Root password is: MinhTho2024"
