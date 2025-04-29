#!/bin/bash

set -e  # ❗ Прерываем выполнение при ошибке любой команды

# 👤 Создание пользователя, если он ещё не существует
id "appuser" &>/dev/null || (echo "➕ Создание пользователя appuser..." && groupadd -g "${HOST_GID}" appuser && useradd -u "${HOST_UID}" -g appuser -m appuser)

echo "📁 Назначение владельца appuser на /var/www/html..."
[ -d "/var/www/html" ] && (chown -R appuser:appuser /var/www/html)

echo "📂 Настройка прав на storage и bootstrap/cache..."
[ -d "/var/www/html/storage" ] && (
    chown -R appuser:www-data /var/www/html/storage
    chmod -R ug+rwX /var/www/html/storage
    find /var/www/html/storage -type d -exec chmod g+s {} \;
)

[ -d "/var/www/html/bootstrap/cache" ] && (
    chown -R appuser:www-data /var/www/html/bootstrap/cache
    chmod -R ug+rwX /var/www/html/bootstrap/cache
    find /var/www/html/bootstrap/cache -type d -exec chmod g+s {} \;
)

# 🐞 Включение/отключение Xdebug
[ "$ENABLE_XDEBUG" = "yes" ] && (echo "✅ Включение Xdebug..." && sed -i 's/xdebug.mode=off/xdebug.mode=debug/' /usr/local/etc/php/conf.d/xdebug.ini) \
    || (echo "🚫 Отключение Xdebug..." && sed -i 's/xdebug.mode=debug/xdebug.mode=off/' /usr/local/etc/php/conf.d/xdebug.ini)

echo "🚀 Запуск PHP-FPM..."
exec php-fpm