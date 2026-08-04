FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php:8.2-cli-alpine AS dependencies

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache libpq-dev libzip-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-install pdo_pgsql mbstring zip bcmath dom simplexml

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor gettext libpq-dev libzip-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-install pdo_pgsql mbstring zip bcmath dom simplexml opcache

WORKDIR /var/www/html

COPY . ./
COPY --from=dependencies /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY docker/nginx.conf.template /etc/nginx/http.d/default.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start-container /usr/local/bin/start-container

RUN chmod +x /usr/local/bin/start-container \
    && mkdir -p storage/app/private storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD ["/usr/local/bin/start-container"]
