# Render has no native PHP runtime, so this Laravel app is deployed as a Docker image.
# Render builds this file, then runs the container and sends traffic to the port in $PORT.

FROM php:8.4-cli

# git + unzip are needed by Composer to download packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 1) Install PHP dependencies first (this layer is cached until composer.lock changes)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# 2) Copy the application code and finish the install
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Safe defaults. Anything you set in Render's "Environment" tab overrides these.
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PHP_CLI_SERVER_WORKERS=4 \
    PORT=10000

USER www-data
EXPOSE 10000

# Cache the config (reads the env vars Render gives the container), then start the server on $PORT.
CMD ["sh", "-c", "php artisan config:cache && php artisan serve --no-reload --host=0.0.0.0 --port=${PORT}"]
