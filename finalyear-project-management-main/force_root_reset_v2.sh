#!/bin/bash
set -e

echo "Stopping MySQL..."
service mysql stop || true

echo "Preparing Socket Directory..."
mkdir -p /var/run/mysqld
chown mysql:mysql /var/run/mysqld

echo "Starting MySQL in Safe Mode..."
# Start mysqld_safe in background without grant tables
mysqld_safe --skip-grant-tables --skip-networking &
PID=$!
sleep 15

echo "Resetting Root Password..."
# Connect as root (no password needed now) and update user
mysql -e "FLUSH PRIVILEGES; ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'MinhTho2024'; FLUSH PRIVILEGES;"

echo "Stopping Safe Mode MySQL..."
kill $PID
sleep 5
pkill mysqld || true

echo "Starting Normal MySQL..."
service mysql start

echo "Verifying Root Access..."
mysql -u root -pMinhTho2024 -e "SELECT 'Root Login OK' as result;"

echo "Updating .env..."
cd /var/www/html/project-management
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=MinhTho2024/' .env

echo "Clearing Caches..."
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

echo "Done! Root password is: MinhTho2024"
