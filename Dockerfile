FROM php:8.2.0-fpm

# Install dependencies
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libzip-dev \
        unzip \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure zip && \
    docker-php-ext-install -j$(nproc) \
        zip \
        pdo \
        pdo_mysql \
        gd \
        intl \
        opcache \
        mbstring \
        exif \
        pcntl \
        bcmath \
        sockets

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Set file permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["php-fpm"]
