FROM wyveo/nginx-php-fpm:php74 as BASE

ARG BUILD_ENV=local
ENV WHITELIST_FUNCTIONS proc_open
ENV COMPOSER_VERSION 1.9.2
ENV DOCUMENT_ROOT=/var/www/html/public
RUN apt-get update && apt-get install -o Dpkg::Options::="--force-confold" --force-yes -y git unzip zip libzip-dev libicu-dev \
    php7.4-bcmath \
    php7.4-intl  \
    php7.4-opcache \
    php7.4-zip \
    php7.4-apcu \
    cron
COPY ./ /var/www/html
RUN ln -sf /var/www/html/var/logs/dev.log /dev/stdout
RUN ln -sf /var/www/html/var/logs/prod.log /dev/stderr
RUN ln -sf /var/log/nginx/error.log /dev/stderr
RUN cp /var/www/html/infrastructure/branch-fetching-cron /etc/cron.d/branch-fetching-cron
RUN cp /var/www/html/infrastructure/site-3.conf /etc/nginx/conf.d/default.conf
RUN chmod 0644 /etc/cron.d/branch-fetching-cron

WORKDIR /var/www/html

#RUN composer install --no-dev --optimize-autoloader \
#    && chown -R www-data:www-data public/ var/ \
#    && bin/console c:c --env=prod
RUN composer install  \
    && chown -R www-data:www-data public/ var/ \
    && bin/console c:c --env=dev
RUN apt-get install -y php7.4-xdebug && phpenmod xdebug

ADD ./infrastructure/start.sh /start.sh
CMD ["/start.sh"]

#FROM BASE as DEV
#RUN apt-get update && apt-get install -y php7.4-xdebug \
#    && php composer.phar install \
#    && bin/console c:c --env=dev
