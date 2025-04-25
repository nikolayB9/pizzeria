FROM php:8.3-fpm

# Обновление пакетов и установка зависимостей, в том числе - редактор nano
RUN apt-get update && apt-get install -y \
    zip unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev git nano \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath

# Устанавливаем Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Удаляем дефолтный файл конфигурации, чтобы не было дублирования
RUN rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Копируем свой xdebug.ini в контейнер
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем entrypoint и даём ему права
COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Используем его при запуске
ENTRYPOINT ["/entrypoint.sh"]