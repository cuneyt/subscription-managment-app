FROM php:8.2.17-fpm
RUN apt-get update -y && apt-get install -y openssl zip unzip git
RUN apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo_mysql 
RUN pecl install mongodb
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini
WORKDIR /app
COPY /subscription-management /app
RUN composer require mongodb/laravel-mongodb:^4.3
RUN composer require predis/predis:^2.0
RUN composer install
CMD php artisan serve --host=0.0.0.0 --port=8181
EXPOSE 8181