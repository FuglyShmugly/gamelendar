# Use official PHP with Apache base image
FROM php:8.3-apache

# Install required system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql gd

# Enable Apache mod_rewrite for modern PHP apps
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your application files
COPY . /var/www/html/

# Set proper permissions for the web server
RUN chown -R www-data:www-data /var/www/html

# Render requires the web service to listen on port 80 or 443
EXPOSE 80
