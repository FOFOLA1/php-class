FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN echo "upload_max_filesize = 20M\npost_max_size = 40M" > /usr/local/etc/php/conf.d/uploads.ini
