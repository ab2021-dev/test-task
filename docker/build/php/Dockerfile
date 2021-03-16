FROM php:7.4.6-fpm-alpine3.10

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
    automake

# advanced php extensions
RUN docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mysqli intl zip \
    && docker-php-ext-install bcmath \
    && pecl install -f mcrypt \
    && docker-php-ext-enable mcrypt \
    && pecl install xdebug-2.9.3 \
    && docker-php-ext-enable xdebug

# custom configuration
COPY php.ini /usr/local/etc/php/php.ini
COPY xdebug.ini $PHP_INI_DIR/conf.d/

# timezone
ENV export TZ=`cat /etc/timezone`

# permissions
RUN addgroup -g ${GROUP} -S workspace
RUN adduser -u ${USER} -S workspace -G workspace

USER workspace
