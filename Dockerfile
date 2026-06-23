FROM php:8.2-apache

WORKDIR /var/www/html

# =========================
# SYSTEM DEPENDENCIES
# =========================
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        zip \
        pdo \
        pdo_mysql \
        mysqli

# =========================
# COMPOSER
# =========================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# =========================
# APACHE CONFIG
# =========================
RUN a2enmod rewrite
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

# =========================
# COPY COMPOSER FILES FIRST (CACHE LAYER)
# =========================
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# =========================
# COPY REST OF PROJECT FILES
# =========================
COPY . .

# =========================
# RUN POST-INSTALL SCRIPTS
# =========================
RUN composer run post-autoload-dump --no-interaction

# =========================
# LARAVEL CACHE CLEAR (SAFE)
# =========================
RUN php artisan config:clear --no-interaction || true
RUN php artisan cache:clear --no-interaction || true
RUN php artisan view:clear --no-interaction || true
RUN php artisan route:clear --no-interaction || true

# =========================
# CREATE STORAGE SYMLINK
# =========================
RUN php artisan storage:link --no-interaction || true

# =========================
# PERMISSION FIX
# =========================
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# =========================
# PORT
# =========================
EXPOSE 80

# =========================
# START APACHE
# =========================
CMD ["apache2-foreground"]
