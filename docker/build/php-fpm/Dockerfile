FROM php:8.2-fpm

# Install dependencies
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
    mariadb-client \
    cron && \
    rm -rf /var/lib/apt/lists/*

# Copy configuration files
COPY opcache.ini /usr/local/etc/php/conf.d/
COPY custom.ini /usr/local/etc/php/conf.d/
COPY crontab /var/spool/cron/crontabs/root
COPY start.sh /start.sh

# Set correct permissions
RUN chmod 600 /var/spool/cron/crontabs/root
# RUN chmod 600 /var/spool/cron/crontabs/www-data
RUN chmod +x /start.sh

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Install PHPUnit
RUN wget --no-check-certificate https://phar.phpunit.de/phpunit-6.5.3.phar && \
    mv phpunit*.phar phpunit.phar && \
    chmod +x phpunit.phar && \
    mv phpunit.phar /usr/local/bin/phpunit

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Modify www-data user to have correct permissions
RUN usermod -u 1000 www-data
RUN usermod -a -G www-data root

# Set up work directory and permissions
RUN mkdir -p /var/www
RUN chown -R www-data:www-data /var/www

RUN mkdir -p /var/www/.composer
RUN chown -R www-data:www-data /var/www/.composer

WORKDIR /var/www/project/

RUN git config --global --add safe.directory /var/www/project

ENTRYPOINT [ "/start.sh" ]