FROM php:8.4-fpm-alpine

# System deps
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zlib-dev \
    mariadb-client \
    nginx \
    busybox-extras \
    openrc \
    supervisor \
    bash \
    git \
    shadow \
    autoconf \
    gcc \
    g++ \
    make \
    && rm -rf /var/cache/apk/*

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip bcmath pcntl gd intl opcache && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    apk del autoconf gcc g++ make

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create non-root user
RUN useradd -G www-data -u 1000 -m www

WORKDIR /var/www/html

COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

CMD ["/usr/local/bin/entrypoint.sh"]
