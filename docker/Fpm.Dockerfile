FROM php:7.4-fpm

RUN apt-get update \
&& apt-get upgrade -y \
&& apt-get install -y git \
&& docker-php-ext-install pdo pdo_mysql

RUN apt-get install -y \
        zlib1g-dev \
        zip \
        unzip

RUN apt-get install -y \
        libpng-dev

RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
	&& docker-php-ext-enable imagick

#RUN docker-php-ext-install mbstring

RUN docker-php-ext-install gd
RUN docker-php-ext-install sockets

# Get latest Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ADD ./docker/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/test-job
