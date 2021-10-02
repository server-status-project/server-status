FROM php:8.0-apache

RUN apt-get update \
  && apt-get install -y --force-yes \
    git \
    wget \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev

RUN apt-get install -y --force-yes mariadb-client

RUN apt-get -y install zlib1g-dev

RUN apt-get update -y \
  && apt-get install -y \
    libxml2-dev \
    vim \
  && apt-get clean -y

RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
  mysqli \
  bcmath \
  gettext \
  gd

RUN apt-get install -y --force-yes ssl-cert

COPY ./conf/custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN echo "Europe/Prague" > /etc/timezone

RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers

COPY ./conf/server-status.localhost.conf /etc/apache2/sites-enabled/000-default.conf
COPY ./conf/ssl-default.conf /etc/apache2/sites-enabled/ssl-default.conf

WORKDIR /app
