FROM php:8.1-cli

RUN docker-php-ext-enable opcache
RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
