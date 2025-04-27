#!/bin/bash

# Запуск app от имени appuser
# Если не указана команда - открываем bash
if [ $# -eq 0 ]; then
  docker compose exec --user appuser app bash
else
  docker compose exec --user appuser app "$@"
fi