<h3 align="center">Laravel console command test application</h3>

`Стягиваем репозиторий на web-сервер, заходим в терминал по этому адресу в корневую директорию`

```sql
# Создаем чистую базу данных
CREATE DATABASE IF NOT EXISTS database 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;
```

```php
// В /config/database.php меняем настройки базы данных на свои:
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'database'),
    'username' => env('DB_USERNAME', 'user'),
    'password' => env('DB_PASSWORD', 'password'),
    ///..
],
```

```bash
# В терминале:

# ставим пакеты
composer install -vvv

# устанавливаем миграции
php artisan migrate:install

# накатываем
php artisan migrate

# тестим команду
php artisan customer:exchange
```

```bash
# файл команды
/app/Console/Commands/CustomerExchange.php
# валидатор dns
/app/Rules/IsValidEmailDns.php
# валидатор rfc
/app/Rules/IsValidEmailRfc2822.php
# Модели:
/app/Models/Customer.php
/app/Models/Location.php
```