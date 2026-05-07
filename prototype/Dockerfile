FROM php:8.2-apache

# Install system dependencies needed for PDO SQLite
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Enable Apache rewrite (optional but useful)
RUN a2enmod rewrite

# Copy project files (adjust if using prototype/)
COPY prototype/ /var/www/html/

# Create writable DB directory
RUN mkdir -p /var/data && chown -R www-data:www-data /var/data

# Permissions
RUN chown -R www-data:www-data /var/www/html

COPY prototype/workouts.db /var/data/workouts.db