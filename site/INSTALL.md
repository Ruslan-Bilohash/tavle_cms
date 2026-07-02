# Bilen CMS — bilohash.com/tavle/site

## URL

- Лендинг скрипта: https://bilohash.com/tavle/
- Сайт (демо): https://bilohash.com/tavle/site/
- Адмін: https://bilohash.com/tavle/site/admin/

## Встановлення

1. Завантажте файли в `/tavle/site/` на сервері
2. Перевірте `config.php`:
   ```php
   define('SITE_URL', 'https://bilohash.com/tavle/site');
   define('BASE_PATH', '/tavle/site');
   ```
3. Права на запис: `data/`, `uploads/cars/`
4. PHP 8+ з розширенням `pdo_sqlite`
5. При першому відвідуванні SQLite база створиться автоматично

## Демо-логін

- Логін: `admin`
- Пароль: `admin123`

## Apache

`.htaccess` містить `RewriteBase /tavle/site/`

## Nginx (приклад)

```nginx
location /tavle/site/ {
    try_files $uri $uri/ /tavle/site/index.php?$query_string;
}
location ~ /tavle/site/data/ {
    deny all;
}
```

## Структура проєкту

```
tavle/
├── index.php          # Лендинг опису скрипта
├── config.php
├── languages/
└── site/              # CMS (каталог авто)
    ├── config.php
    ├── data/bilen.sqlite
    └── admin/
```