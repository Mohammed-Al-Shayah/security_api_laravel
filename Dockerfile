# Use the official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies + PostgreSQL extension
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_pgsql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache DocumentRoot to /public (Laravel)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy app files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Make sure storage & cache folders موجودة وقابلة للكتابة
RUN mkdir -p storage/framework/views storage/framework/cache storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

# Expose port 80 (Render بيشوفه تلقائيًا)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
