FROM php:8.1

RUN apt-get update -y \
    && apt full-upgrade --quiet --yes \
    && apt-get install -y \
    unzip \
    libpq-dev \
    libcurl4-gnutls-dev

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www
COPY . .

# Install composer
COPY --from=composer:2.7.1 /usr/bin/composer /usr/bin/composer

RUN chmod +x ./docker/entrypoint-install.sh

ENV PORT=8000
ENTRYPOINT [ "docker/entrypoint-install.sh" ]