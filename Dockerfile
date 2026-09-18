# Production Dockerfile for Laravel on Railway
FROM php:8.1-apache

# Install system dependencies & PHP extensions required by Laravel
RUN apt-get update && apt-get install -y --no-install-recommends     libpng-dev     libjpeg-dev     libfreetype6-dev     libzip-dev     zip     unzip     git     curl     libonig-dev     libxml2-dev     && docker-php-ext-configure gd --with-freetype --with-jpeg     && docker-php-ext-install -j$(nproc)         pdo         pdo_mysql         mbstring         zip         exif         pcntl         bcmath         gd         opcache     && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite and headers modules
RUN a2enmod rewrite headers

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure directory overrides so .htaccess and route rewrites work properly
RUN printf '<Directory /var/www/html>\n    Options -Indexes +FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/override.conf && a2enconf override

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Run composer install to optimize autoloader for production
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Ensure storage directories exist and have proper permissions for Apache
RUN mkdir -p     storage/framework/sessions     storage/framework/views     storage/framework/cache     storage/logs     bootstrap/cache     && chown -R www-data:www-data storage bootstrap/cache     && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose default port (Railway dynamically overrides via $PORT environment variable)
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
