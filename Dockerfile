FROM php:8.2-fpm

# Install system dependencies (gettext-base = envsubst)
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libpq-dev zip unzip nginx gettext-base

# PHP extensions (PostgreSQL support ke sath)
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . /var/www

# Install production dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Entrypoint (nginx config Render/railway ke PORT par bind karta hai)
RUN chmod +x /var/www/docker-entrypoint.sh

EXPOSE 80
CMD ["/var/www/docker-entrypoint.sh"]
