#!/bin/bash

set -e  # Прерываем выполнение, если какая-то команда завершится с ошибкой

echo "🔧 Установка прав на папки storage и bootstrap..."

# Проверяем, существует ли папки, если существуют:
# меняем владельца папок на www-data (пользователь, от которого работает PHP в контейнере)
# устанавливаем права доступа: владельцу и группе - чтение, запись, выполнение; другим - чтение и выполнение
if [ -d "/var/www/html/storage" ]; then
  chown -R www-data:www-data /var/www/html/storage
  chmod -R 775 /var/www/html/storage
fi

if [ -d "/var/www/html/bootstrap/cache" ]; then
  chown -R www-data:www-data /var/www/html/bootstrap/cache
  chmod -R 775 /var/www/html/bootstrap/cache
fi

echo "🚀 Запуск PHP-FPM..."
exec php-fpm