FROM php:8.0.12

WORKDIR /app

RUN apt update && apt install -y git libzip-dev zip libsodium-dev && \ 
    docker-php-ext-install zip sodium && \
    docker-php-ext-enable sodium

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer