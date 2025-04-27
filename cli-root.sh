#!/bin/bash

# Запуск app от имени root
# Если не указана команда - открываем bash
if [ $# -eq 0 ]; then
  docker compose exec app bash
else
  docker compose exec app "$@"
fi