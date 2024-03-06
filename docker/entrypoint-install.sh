#!/bin/bash

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
    cp .env .env
fi

php artisan migrate
php artisan key:generate

php artisan serve --port=$PORT --host=0.0.0.0 --env=.env &

npm run dev -- --host
exec docker-php-entrypoint "$@"