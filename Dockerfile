FROM php:8.2-apache

# Install required PHP extensions (PDO, PostgreSQL)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Enable Apache mod_rewrite for URL routing
RUN a2enmod rewrite

# Copy the application code to the Apache document root
COPY . /var/www/html/

# Set the correct permissions for the web directory
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80
