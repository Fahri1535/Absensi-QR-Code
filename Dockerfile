FROM php:8.2-cli

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    default-mysql-client

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Copy project FIRST (IMPORTANT FIX)
COPY . .

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# FIX: install AFTER artisan exists
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000