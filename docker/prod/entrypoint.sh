#!/bin/bash

php artisan migrate --force

service php8.2-fpm start

nginx -g "daemon off;"