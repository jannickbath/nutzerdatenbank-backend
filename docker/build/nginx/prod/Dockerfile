FROM nginx:latest

COPY default.conf /etc/nginx/conf.d/
COPY ../../../../public/index.php /var/www/public/index.php

RUN echo "upstream php-upstream { server fpm:9000; }" > /etc/nginx/conf.d/upstream.conf
RUN usermod -u 1000 www-data