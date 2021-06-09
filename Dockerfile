FROM php:8.0-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install YAML extension
RUN apt-get update -y
RUN apt-get install libyaml-dev -y
RUN pecl install yaml && echo "extension=yaml.so" > /usr/local/etc/php/conf.d/ext-yaml.ini && docker-php-ext-enable yaml

# Install git
RUN apt-get -y install git