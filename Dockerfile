FROM php:8.2-cli

WORKDIR /app

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
        pdo_mysql

# =========================
# COMPOSER
# =========================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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
RUN composer run post-autoload-dump

# =========================
# LARAVEL CACHE CLEAR (SAFE)
# =========================
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan view:clear || true
RUN php artisan route:clear || true

# =========================
# PERMISSION FIX
# =========================
RUN chmod -R 777 storage bootstrap/cache

# =========================
# PORT RAILWAY
# =========================
EXPOSE 8000

# =========================
# START APP
# =========================
CMD php -S 0.0.0.0:8000 -t public
