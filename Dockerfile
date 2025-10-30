FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-libmysqlclient-dev \
    libonig-dev \
    libicu-dev \
    && docker-php-ext-install mysqli mbstring intl

COPY . /var/www/html/

# Mantenemos el custom.ini para ver errores
COPY custom.ini /usr/local/etc/php/conf.d/custom-errors.ini
