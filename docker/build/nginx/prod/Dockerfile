FROM nginx:latest

COPY docker/build/nginx/prod/default.conf /etc/nginx/conf.d/
# COPY public/index.php /var/www/public/index.php
# Build Context is the project root, specified in .github/workflows/deploy.yml
COPY . /var/www/project

RUN echo "upstream php-upstream { server fpm:9000; }" > /etc/nginx/conf.d/upstream.conf
RUN usermod -u 1000 www-data