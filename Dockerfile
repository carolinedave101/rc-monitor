FROM php:8.5-cli

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libzip-dev unzip \
    && docker-php-ext-install -j"$(nproc)" mbstring zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN php -m | grep -qi pdo_sqlite || docker-php-ext-install pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_BUSY_TIMEOUT=5000 \
    DB_JOURNAL_MODE=wal \
    SESSION_DRIVER=database \
    SESSION_ENCRYPT=false \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    MAIL_MAILER=log \
    PHP_CLI_SERVER_WORKERS=8

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 7860

ENTRYPOINT ["entrypoint.sh"]
