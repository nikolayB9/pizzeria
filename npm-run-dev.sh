#!/bin/bash

# Проверяем, есть ли файлы в node_modules внутри контейнера
if ! docker compose exec --user appuser app test "$(docker compose exec --user appuser app sh -c 'ls -A /var/www/html/node_modules')"; then
    echo "node_modules пустой. Выполняем npm install..."
    docker compose exec --user appuser app npm install
else
    echo "node_modules не пустой. Пропускаем npm install."
fi

docker compose exec --user appuser app npm run dev
