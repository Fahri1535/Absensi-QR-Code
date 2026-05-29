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
    nodejs \
    npm \
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
# COPY PROJECT
# =========================
COPY . .

# =========================
# INSTALL BACKEND DEPENDENCIES
# =========================
RUN composer install --no-dev --optimize-autoloader --no-interaction

# =========================
# INSTALL FRONTEND + BUILD VITE (FIX CSS HILANG)
# =========================
RUN npm install
RUN npm run build

# =========================
# LARAVEL OPTIMIZE
# =========================
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan view:clear || true
RUN php artisan route:clear || true

RUN php artisan config:cache || true
RUN php artisan route:cache || true

# =========================
# PERMISSION FIX
# =========================
RUN chmod -R 777 storage bootstrap/cache

# =========================
# PORT RAILWAY
# =========================
EXPOSE 8000

# =========================
# START COMMAND
# =========================
CMD php artisan serve --host=0.0.0.0 --port=8000