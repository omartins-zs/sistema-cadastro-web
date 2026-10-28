FROM composer:2 AS dependencies

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libxml2-dev \
    && docker-php-ext-install dom mbstring mysqli opcache \
    && a2enmod rewrite \
    && apt-get purge -y --auto-remove libonig-dev libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/php/local.ini /usr/local/etc/php/conf.d/zz-local.ini
COPY docker/apache/performance.conf /etc/apache2/conf-available/zz-performance.conf
RUN a2enconf zz-performance

COPY --from=dependencies --chown=www-data:www-data /app/vendor/ /var/www/html/vendor/
COPY --chown=www-data:www-data . /var/www/html

EXPOSE 80
