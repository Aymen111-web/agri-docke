# Production Dockerfile for Laravel 13 REST API
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install build dependencies, configure & compile required PHP extensions, then clean up dev headers
RUN apk add --no-cache \
    bash \
    curl \
    git \
    freetype \
    libpng \
    libjpeg-turbo \
    libwebp \
    icu-libs \
    libzip \
    oniguruma \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        opcache \
        zip \
        intl \
        gd \
        pcntl \
        posix \
        exif \
    && apk del --no-network freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev icu-dev libzip-dev oniguruma-dev linux-headers

# Copy Composer binary from official Composer image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Copy composer manifest and lockfile first for Docker layer caching
COPY composer.json composer.lock ./

# Install production dependencies without running scripts
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist --optimize-autoloader

# Copy application source code
COPY . .

# Complete composer autoloading optimization
RUN composer dump-autoload --optimize --no-dev

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Ensure storage and bootstrap cache directories exist and have proper permissions
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script and ensure Unix line endings + execute permission
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose internal PHP-FPM FastCGI port
EXPOSE 9000

# Set entrypoint and default command
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
