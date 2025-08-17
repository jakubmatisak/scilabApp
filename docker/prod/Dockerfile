FROM ubuntu:22.04

ENV DEBIAN_FRONTEND noninteractive

# Install Laravel dependencies
RUN apt update
RUN apt install -y software-properties-common
RUN add-apt-repository -y ppa:ondrej/php
RUN apt-get update
RUN apt-get install -y php8.2\
    php8.2-cli\
    php8.2-common\
    php8.2-fpm\
    php8.2-zip\
    php8.2-gd\
    php8.2-mbstring\
    php8.2-curl\
    php8.2-xml\
    php8.2-bcmath\
    php8.2-pdo\
    php8.2-mysql

# Install composer for Laravel
RUN apt-get install -y curl && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js to run Vue application
RUN apt-get install -y ca-certificates gnupg
RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
ENV NODE_MAJOR 18
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update
RUN apt-get install -y nodejs

# Install scilab and its dependencies
RUN cd /tmp \
    && apt-get install -y wget libx11-6 libjogl2-java libtinfo5 libxft2 libxext6 libxtst6 \
    && wget https://www.scilab.org/download/6.0.2/scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && tar -xzf scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && mv scilab-6.0.2/ /opt/scilab \
    && chown -R root:root /opt/scilab \
    && rm scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && mkdir -p /scripts

# Add permissions for running script
RUN chown -R root:root /root/

RUN mkdir scilabApp

# Setup Laravel
WORKDIR /var/www/scilabApp
COPY . .
RUN chown -R www-data:www-data /var/www
COPY .env.production .env
RUN mkdir -p storage/app/experiment_files \
    && chown -R www-data:www-data storage/app/experiment_files

# Insall php dependencies and run php artisan basic actions
RUN composer install --no-progress --no-interaction

RUN npm install \
    && npm run build

# Install and setup Nginx
RUN apt-get install nginx -y
COPY ./docker/prod/scilabApp.conf /etc/nginx/sites-available/scilabApp.conf
RUN ln -s /etc/nginx/sites-available/scilabApp.conf /etc/nginx/sites-enabled/ \
    && cp ./docker/prod/certificates/webte_fei_stuba_sk.pem /etc/ssl/certs/ \
    && cp ./docker/prod/certificates/webte.fei.stuba.sk.key /etc/ssl/private/

# Setup php-fpm
RUN mkdir -p /run/php/ \
    && chown www-data:www-data /run/php/ \
    && chmod 755 /run/php/

RUN chmod +x docker/prod/entrypoint.sh \
    && chmod +x docker/run-script.sh

EXPOSE 80
ENTRYPOINT [ "/bin/bash", "-c" ]
CMD ["docker/prod/entrypoint.sh"]
