#!/bin/bash
set -e

echo "Starting Final Configuration..."
cd /var/www/html/project-management

# 1. Permissions
echo "Setting Permissions..."
sudo chown -R ubuntu:www-data .
sudo chmod -R 775 storage bootstrap/cache

# 2. Artisan Finalization
echo "Finalizing Artisan Commands..."
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan shield:setup --yes || true

# 3. Nginx Config
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

echo "Deployment Finalized Successfully!"
