FROM php:8.2-apache

RUN apt-get update && apt-get install -y default-libmysqlclient-dev libonig-dev \
    && docker-php-ext-install mysqli mbstring json

COPY . /var/www/html/
