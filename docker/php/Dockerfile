FROM php:8.3-fpm

# Обновление пакетов и установка зависимостей
RUN apt-get update && apt-get install -y \
    zip unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev git nano \
    # Устанавливаем Node.js (вместе с npm)
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath \
    && rm -rf /var/lib/apt/lists/*  # Удаляем кэш apt-get, чтобы уменьшить размер образа

# Устанавливаем Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Удаляем дефолтный файл конфигурации, чтобы не было дублирования
RUN rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Копируем свой xdebug.ini в контейнер
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Добавляем аргументы
ARG HOST_UID
ARG HOST_GID

# Создаем пользователя с нужным UID и GID
RUN groupadd -g ${HOST_GID} appuser \
   && useradd -u ${HOST_UID} -g appuser -m appuser

# Копируем entrypoint и даём ему права
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Используем его при запуске
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]