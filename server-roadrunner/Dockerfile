FROM php:8.2 as build

ARG COMPOSER_ALLOW_SUPERUSER=1
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y git libzip-dev

RUN docker-php-ext-install zip sockets

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY composer.json /build/composer.json

COPY . /build
WORKDIR /build
RUN composer install
RUN vendor/bin/rr get-binary

FROM php:8.2-cli

RUN docker-php-ext-enable opcache
RUN docker-php-ext-install pcntl


RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
RUN echo 'opcache.enable_cli=1\nopcache.jit_buffer_size=16M' > $PHP_INI_DIR/conf.d/zzz-custom.ini

COPY --from=build /build /app

EXPOSE 8080
WORKDIR /app

ENTRYPOINT /app/rr serve
