#!/bin/sh

echo "Starting Container"
cron -f &
php-fpm