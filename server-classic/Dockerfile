FROM php:8.2 as build

ARG COMPOSER_ALLOW_SUPERUSER=1
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y git libzip-dev

RUN docker-php-ext-install zip

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /build

WORKDIR /build
RUN composer install

FROM php:8.2-fpm

ENV PORT=8080

RUN docker-php-ext-enable opcache
RUN pecl install redis && docker-php-ext-enable redis

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
RUN echo 'opcache.jit_buffer_size=16M' > $PHP_INI_DIR/conf.d/zzz-custom.ini
RUN echo 'listen = /run/php/fpm.sock' > $PHP_INI_DIR/../php-fpm.d/zzz-custom.conf

COPY --from=build /build /app
COPY --from=nginx:latest /usr/sbin/nginx /usr/sbin/nginx
COPY --from=nginx:latest /var/log/nginx /var/log/nginx
COPY --from=nginx:latest /var/cache/nginx /var/cache/nginx
COPY ./docker/entrypoint.sh /entrypoint.sh
COPY ./docker/nginx /etc/nginx

RUN mkdir -p /run/php

EXPOSE $PORT
WORKDIR /app

ENTRYPOINT /entrypoint.sh
