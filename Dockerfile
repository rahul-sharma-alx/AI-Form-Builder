############
#  Stage 1: build frontend assets with Vite
############
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

############
#  Stage 2: PHP runtime
############
FROM php:8.3-cli

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
        libzip-dev libonig-dev libxml2-dev libpq-dev zlib1g-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd zip mbstring bcmath pdo_mysql pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts --no-interaction

COPY . .

COPY --from=assets /app/public/build /app/public/build

RUN chown -R www-data:www-data storage bootstrap/cache

USER www-data

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT"]
