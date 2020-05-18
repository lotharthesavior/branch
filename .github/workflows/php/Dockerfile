FROM php:7.4.5-fpm
  
RUN apt-get update

# Install PDO and PGSQL Drivers
RUN apt-get install -y libpq-dev libpng-dev

# Install php extensions
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
  && docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
  && docker-php-ext-install pdo pdo_mysql mysqli \
  && docker-php-ext-install json \
  && docker-php-ext-install gd

#install some base extensions
RUN apt-get install -y libzip-dev zip \
  && docker-php-ext-install zip

# Install debug dependencies
RUN apt-get install git -y \
    && apt-get install curl -y \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# install git
RUN git config --global user.email "john@doe.com" \
        && git config --global user.name "John Doe"

# copy gitconfig into www-data user directory
RUN cp ~/.gitconfig /var/www/.gitconfig