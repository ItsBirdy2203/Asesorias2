FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-libmysqlclient-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-install mysqli mbstring xml intl

COPY . /var/www/html/
