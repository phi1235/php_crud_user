# Simple PHP + Apache image with mysqli enabled
FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mods
RUN a2enmod rewrite

# Configure Apache to allow .htaccess overrides
RUN sed -i 's#<Directory /var/www/>#<Directory /var/www/>\n    AllowOverride All#' /etc/apache2/apache2.conf

# Copy a basic php.ini (optional)
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Workdir
WORKDIR /var/www/html
