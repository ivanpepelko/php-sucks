FROM php:8.2 as build

ARG COMPOSER_ALLOW_SUPERUSER=1
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y git libzip-dev

RUN docker-php-ext-install zip

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY composer.json /build/composer.json


WORKDIR /build
RUN composer install

FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y git libuv1-dev

RUN docker-php-ext-enable opcache
RUN docker-php-ext-install pcntl

RUN docker-php-source extract  \
    && git clone --depth 1 https://github.com/amphp/ext-uv.git \
    && cd ext-uv \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable uv \
    && docker-php-source delete

WORKDIR /
RUN rm -rf /ext-uv

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
RUN echo 'opcache.enable_cli=1\nopcache.jit_buffer_size=16M' > $PHP_INI_DIR/conf.d/zzz-custom.ini

COPY --from=build /build/vendor /app/vendor
COPY main.php /app/main.php

EXPOSE 8080
WORKDIR /app

ENTRYPOINT php main.php
