FROM php:8.4-cli-alpine AS vendor
RUN apk add --no-cache curl git unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

FROM php:8.4-fpm-alpine
RUN apk add --no-cache \
        libzip-dev \
        zip \
        unzip \
        curl \
        nginx \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /usr/local/bin/composer /usr/local/bin/composer
RUN composer dump-autoload --no-dev --optimize

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache resources/views \
    && chown -R www-data:www-data storage bootstrap/cache resources/views \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx-backend.conf /etc/nginx/http.d/default.conf
COPY docker/php-uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /run/nginx /var/log/nginx /var/cache/nginx

EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
