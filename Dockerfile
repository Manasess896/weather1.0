FROM php:8.1-apache

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    curl \
    git \
    libssl-dev \
    pkg-config \
    && docker-php-ext-install zip pdo pdo_mysql
RUN a2enmod rewrite
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock* /var/www/html/
WORKDIR /var/www/html
RUN if [ -f "composer.json" ]; then \
    composer install --no-interaction --optimize-autoloader --no-dev --no-scripts --no-autoloader \
    ; fi

COPY . /var/www/html/
RUN if [ -f "composer.json" ]; then \
    composer dump-autoload --optimize --no-dev \
    ; fi

RUN chown -R www-data:www-data /var/www/html/

RUN chown -R www-data:www-data /var/www/html/

ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/../!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
EXPOSE 80

CMD ["apache2-foreground"]
