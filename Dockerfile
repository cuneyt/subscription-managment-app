FROM php:8.2.17-fpm
RUN apt-get update -y && apt-get install -y openssl zip unzip git cron curl libcurl4-openssl-dev pkg-config libssl-dev
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo_mysql
RUN pecl install mongodb
RUN docker-php-ext-enable mongodb
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini

WORKDIR /app
COPY /subscription-management /app

RUN composer require mongodb/laravel-mongodb:^4.3 predis/predis:^2.0
RUN composer install

RUN crontab -l | { cat; echo "30 * * * * curl http://localhost:8181/api/worker >> /var/log/cron-worker.log 2>&1"; } | crontab -
RUN touch /var/log/cron.log
COPY start-cron.sh /usr/local/bin/start-cron.sh
RUN chmod +x /usr/local/bin/start-cron.sh
CMD ["/usr/local/bin/start-cron.sh"]
EXPOSE 8181
