FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive 
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy all files & folders into /project
COPY . /project

WORKDIR /project

# Install PHP, necessary extensions, and Nginx
RUN apt update && apt upgrade --yes
RUN apt-get update && apt-get install -y \
    curl \
    nginx \
    php8.1 \
    php8.1-fpm \
    php8.1-opcache \
    php8.1-gd \
    php8.1-mysqli \
    php8.1-curl \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-zip \
    php8.1-tokenizer \
    php8.1-ctype \
    php8.1-pdo \
    php8.1-phar \
    php8.1-dom

# Install Symfony CLI
RUN ["/bin/bash", "-c", "curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash"]
RUN apt install symfony-cli -y

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /bin/composer

# Install dependencies
RUN composer install --no-scripts
RUN composer require --no-scripts symfony/maker-bundle --dev
RUN composer require --no-scripts orm symfony/serializer symfony/property-access nelmio/cors-bundle

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Start PHP-FPM and Nginx
CMD service php8.1-fpm start && nginx -g 'daemon off;'

# Expose HTTP port
EXPOSE 80
