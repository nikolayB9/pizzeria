### Изменение ролей пользователя

- Делаем изменения в UserRoleEnum (нельзя удалять / изменять существующие value и slug !)
- Запускаем UserRoleSeeder:
    ```bash
    php artisan db:seed --class=UserRoleSeeder
    ```