FROM php:8.2-apache

RUN apt-get update
RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip

RUN apt-get update && apt-get install -y \
    imagemagick libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick

RUN apt-get update && apt-get install -y python3 python3-pip

RUN a2enmod rewrite

RUN apt-get -y install cron

RUN touch /var/log/cron.log
RUN (echo "* * * * * /var/cron/cron.sh") | crontab
ENTRYPOINT ["sh", "/var/scripts/docker-start.sh"]
