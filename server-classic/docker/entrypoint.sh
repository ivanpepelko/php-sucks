#!/usr/bin/env bash

echo "listen $PORT default_server;" > /etc/nginx/conf.d/listen

#trap "killall5 2" INT TERM QUIT
nginx
php-fpm
