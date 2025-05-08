-- Создание рабочей базы данных (если её нет)
CREATE DATABASE IF NOT EXISTS pizzeria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Создание тестовой базы данных (если её нет)
CREATE DATABASE IF NOT EXISTS pizzeria_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Создание пользователя с привилегиями для тестовой базы данных
CREATE USER IF NOT EXISTS 'admin_pizzeria'@'%' IDENTIFIED BY 'secret';

-- Привилегии для тестовой базы данных
GRANT ALL PRIVILEGES ON pizzeria_test.* TO 'admin_pizzeria'@'%';

-- Привилегии для рабочей базы данных (если нужно)
GRANT ALL PRIVILEGES ON pizzeria.* TO 'admin_pizzeria'@'%';

-- Применение привилегий
FLUSH PRIVILEGES;
