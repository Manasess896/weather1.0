FROM php:8.1-apache

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    curl \
    git \
    && docker-php-ext-install zip pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files first for better layer caching
COPY composer.json composer.lock* /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install PHP dependencies
RUN if [ -f "composer.json" ]; then \
    composer install --no-interaction --optimize-autoloader --no-dev --no-scripts --no-autoloader \
    ; fi

# Copy the rest of the application
COPY . /var/www/html/

# Finish composer installation
RUN if [ -f "composer.json" ]; then \
    composer dump-autoload --optimize --no-dev \
    ; fi

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

# Configure Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/../!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
