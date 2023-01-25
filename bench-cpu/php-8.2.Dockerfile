FROM php:8.2-cli

RUN docker-php-ext-enable opcache
RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
