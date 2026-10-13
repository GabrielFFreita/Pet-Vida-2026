FROM php:8.2-apache-bookworm

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod 775 /var/www/html/uploads

EXPOSE 80
