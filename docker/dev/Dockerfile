FROM ubuntu:22.04

ENV DEBIAN_FRONTEND noninteractive

# Install Laravel dependencies
RUN apt update
RUN apt install -y software-properties-common
RUN add-apt-repository -y ppa:ondrej/php
RUN apt update
RUN apt install -y php8.2\
    php8.2-cli\
    php8.2-common\
    php8.2-fpm\
    php8.2-mysql\
    php8.2-zip\
    php8.2-gd\
    php8.2-mbstring\
    php8.2-curl\
    php8.2-xml\
    php8.2-bcmath\
    php8.2-pdo

# Install composer for Laravel
RUN apt install -y curl && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js to run Vue application
RUN apt install -y ca-certificates gnupg
RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
ENV NODE_MAJOR 18
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt update
RUN apt install -y nodejs

# Install scilab and its dependencies
RUN cd /tmp \
    && apt install -y wget libx11-6 libjogl2-java libtinfo5 libxft2 libxext6 libxtst6 \
    && wget https://www.scilab.org/download/6.0.2/scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && tar -xzf scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && mv scilab-6.0.2/ /opt/scilab \
    && chown -R root:root /opt/scilab \
    && rm scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && mkdir -p /scripts

# Copy run-script for executing scilab simulations
COPY docker/run-script.sh /scripts/

# Add permissions for running script
RUN chown -R root:root /root/ \
    && chmod +x /scripts/run-script.sh

RUN mkdir scilabApp

# Setup Laravel
WORKDIR /var/www/scilabApp
COPY . .
RUN chown -R www-data:www-data /var/www

RUN npm install

RUN chmod +x ./docker/dev/entrypoint-install.sh ./docker/dev/wait-for-it.sh
ENV PORT=8000
ENTRYPOINT [ "/bin/bash", "-c" ]
CMD [ "docker/dev/wait-for-it.sh $DB_HOST:$DB_PORT --strict --quiet -- docker/dev/entrypoint-install.sh" ]
