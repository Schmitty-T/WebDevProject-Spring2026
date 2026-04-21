FROM php:8.2-apache

# Install SQLite support
RUN docker-php-ext-install pdo pdo_sqlite

# Enable Apache rewrite (for routing, optional but useful)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy your app into the container
COPY prototype/ /var/www/html/

# Create a writable directory for SQLite
RUN mkdir -p /var/data && chown -R www-data:www-data /var/data

# Also ensure your app files are readable
RUN chown -R www-data:www-data /var/www/html