FROM php:7.4-apache
RUN docker-php-ext-install mysqli

# Install YAML extension
RUN apt-get update -y
RUN apt-get install libyaml-dev -y
RUN pecl install yaml && echo "extension=yaml.so" > /usr/local/etc/php/conf.d/ext-yaml.ini && docker-php-ext-enable yaml

# Install AWS sdk
RUN apt-get install git
RUN cd /usr
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN mv composer.phar /usr/local/bin/composer
RUN cd /var/www/html/
RUN composer require aws/aws-sdk-php