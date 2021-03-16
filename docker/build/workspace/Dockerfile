FROM php:7.4.6-cli-alpine3.10

ARG USER=1000
ARG GROUP=1000

WORKDIR /var/www

# system dependecies and utils
RUN apk add libmcrypt-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    make \
    g++ \
    autoconf \
    automake \
    openssl \
    nano \
    mc \
    wget \
    bash \
    git

# advanced php extensions
RUN docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mysqli intl zip \
    && docker-php-ext-install bcmath \
    && pecl install -f mcrypt \
    && docker-php-ext-enable mcrypt

# custom configuration
COPY php.ini /usr/local/etc/php/php.ini

# composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer --version=2.0.3

# timezone
ENV export TZ=`cat /etc/timezone`

# console colors
ENV TERM xterm-256color

# permissions
RUN addgroup -g ${GROUP} -S workspace
RUN adduser -u ${USER} -S workspace -G workspace --shell=/bin/sh

USER workspace
