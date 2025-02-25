FROM php:8.2-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    zlib1g-dev \
    libxml2-dev \
    libpng-dev \
    libzip-dev \
    vim \
    curl \
    debconf \
    subversion \
    git \
    apt-transport-https \
    apt-utils \
    build-essential \
    locales \
    acl \
    mailutils \
    wget \
    nodejs \
    zip \
    unzip \
    gnupg \
    gnupg1 \
    gnupg2 \
    sudo \
    ssh  \
    # docker-php-ext-install \
    # pdo_mysql \
    # soap \
    zip 
    # opcache \
    # gd \
    # intl

COPY docker/build/php-fpm/opcache.ini /usr/local/etc/php/conf.d/
COPY docker/build/php-fpm/custom.ini /usr/local/etc/php/conf.d/

# COPY public/index.php /var/www/public/index.php
# Build Context is the project root, specified in .github/workflows/deploy.yml
COPY . /var/www/project

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN composer self-update 1.9.0

RUN wget --no-check-certificate https://phar.phpunit.de/phpunit-6.5.3.phar && \
    mv phpunit*.phar phpunit.phar && \
    chmod +x phpunit.phar && \
    mv phpunit.phar /usr/local/bin/phpunit

RUN usermod -u 1000 www-data

RUN usermod -a -G www-data root

RUN mkdir -p /var/www
RUN chown -R www-data:www-data /var/www

RUN mkdir -p /var/www/.composer
RUN chown -R www-data:www-data /var/www/.composer

WORKDIR /var/www/project/
