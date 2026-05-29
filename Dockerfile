FROM php:8.2-cli

WORKDIR /app

COPY . .

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip \
    libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        zip \
        pdo \
        pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000