FROM php:8.0-cli

RUN docker-php-ext-enable opcache
RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
