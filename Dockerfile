FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-libmysqlclient-dev \
    libonig-dev \
    && docker-php-ext-install mysqli mbstring

COPY . /var/www/html/

# Mantenemos el custom.ini para ver errores si algo más falla
COPY custom.ini /usr/local/etc/php/conf.d/custom-errors.ini
