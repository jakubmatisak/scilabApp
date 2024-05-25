# Scilab module

## Description

Scilab module is one of the building block for Online Virtual Laboratory (**OVL**).

## Table of Contents

-   [Setup DEV](#setup-dev)
-   [Installation](#installation)
-   [Running the application](#running-the-application)
-   [Scilab installation](#scilab-installation-optional---only-for-running-locally)
-   [Deploy](#deploy)

## Setup DEV

Copy .env.example file to .env

### Setup Database

-   **DB_DATABASE** - Specify the database name
-   **DB_USERNAME** - Specify the username to connect to database
-   **DB_PASSWORD** - Specify the user password to connect to database

(Database root password will be generated randomly, to get it, follow the logs in the container)

### (Optional)

Database is setup to be running in a docker container. For running on a different database change following:

-   **DB_CONNECTION** - Specify the type of database connection, such as mysql, postgres, sqlite.
-   **DB_HOST** - Specify the url on which database is running
-   **DB_PORT** - Specify the port on which database is running

## Installation

```sh
composer install
npm install
```

**After installation run:** `php artisan key:generate`

## Running the application

### To run the application in docker, run:

```sh
docker compose -f docker-compose.dev.yml up -d
```

### To run the application locally on your linux machine, run:

```sh
php artisan migrate
php artisan serve
npm run dev
```

**IMPORTANT**: Note, that for running the application locally, you need to have scilab installed. For this, continue to Scilab installation

## Scilab Installation (Optional - only for running locally)

To install Scilab locally on your Ubuntu machine, run:

```sh
sudo wget https://www.scilab.org/download/6.0.2/scilab-6.0.2.bin.linux-x86_64.tar.gz
sudo tar -xzf scilab-6.0.2.bin.linux-x86_64.tar.gz
sudo mv scilab-6.0.2/ /opt/scilab
sudo chown -R root:root /opt/scilab
sudo rm scilab-6.0.2.bin.linux-x86_64.tar.gz
```

This ensures, that you have scilab application in the correct folder to be executable by [run-script.sh](/docker/run-script.sh). Alternatively, you can change the location of scilab in the file, but beware. The file is also being used in docker container, so changing it, might break running scilab in the docker.

## DEPLOY

Copy .env.example file to .env

```sh
cp .env.example .env
```

and run `php artisan key:generate`\
After that, change the file name to `.env.production`

Change **APP_URL** to url on which the app would be running\
Change **API_URL** to url to the _APP_URL_ and append `/api` to it

### Setup Database

-   **DB_DATABASE** - Specify the database name
-   **DB_USERNAME** - Specify the username to connect to database
-   **DB_PASSWORD** - Specify the user password to connect to database

(Database root password will be generated randomly, to get it, follow the logs in the container)

### Setup key and cert

Upload your `.key` and `.pem`/ `.cert` file to `/docker/prod/certificates`\
Search the project for `webte_fei_stuba_sk.pem` and `webte.fei.stuba.sk.key` and replace it with the names of your files

### Deploy the containers

Make sure that your server has Docker installed\
Run `docker compose -f docker-compose.prod.yml up -d`
