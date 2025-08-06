FROM php:8.1-apache

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    curl \
    git \
    libssl-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Install MongoDB PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application files
COPY . /var/www/html/

# Install PHP dependencies
WORKDIR /var/www/html
RUN if [ -f "composer.json" ]; then composer install --no-interaction --optimize-autoloader --no-dev; fi

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
