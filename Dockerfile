FROM php:8.3-cli-alpine AS sio_test
RUN apk add --no-cache git zip bash

RUN apk add --no-cache git zip bash

# 1) Устанавливаем build tools (autoconf, make, gcc, etc.) + linux-headers и zlib-dev для Xdebug
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    gcc \
    g++ \
    make \
    linux-headers \
    zlib-dev \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Setup php extensions
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql

ENV COMPOSER_CACHE_DIR=/tmp/composer-cache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG USER_ID=1000
RUN adduser -u ${USER_ID} -D -H app
USER app

COPY --chown=app . /app
WORKDIR /app

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

EXPOSE 8337

CMD ["php", "-S", "0.0.0.0:8337", "-t", "public"]