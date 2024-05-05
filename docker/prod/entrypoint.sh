#!/bin/bash

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

service php8.2-fpm start

nginx -g "daemon off;"