# ==========================================
# PHP 8.5 + Apache 2.4 Runtime
# ==========================================
FROM php:8.5-apache

# Set Timezone selaras dengan konfigurasi lokal (Asia/Makassar)
ENV TZ=Asia/Makassar
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install dependensi sistem & ekstensi PHP sesuai spesifikasi lokal
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        mbstring \
        xml \
        zip \
        exif \
        pcntl \
        opcache \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Konfigurasi php.ini sesuai spesifikasi lokal
RUN { \
    echo "upload_max_filesize=2M"; \
    echo "post_max_size=8M"; \
    echo "memory_limit=-1"; \
    echo "max_execution_time=0"; \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=0"; \
    echo "date.timezone=Asia/Makassar"; \
} > /usr/local/etc/php/conf.d/sim-mbg.ini

# Arahkan DocumentRoot Apache ke /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy Composer 2.10
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy file composer terlebih dahulu agar layer cache optimal
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy seluruh source code aplikasi
COPY . .

# Jalankan script post-autoload-dump & atur permission storage/cache Laravel
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
