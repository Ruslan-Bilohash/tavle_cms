# BILOHASH Tavle / Bilen CMS — demo / self-host container (PHP 8.2 + Apache + SQLite)
FROM php:8.2-apache-bookworm

RUN a2enmod rewrite headers \
    && apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p site/data site/uploads/cars \
    && chown -R www-data:www-data site/data site/uploads \
    && chmod 750 site/data

EXPOSE 80