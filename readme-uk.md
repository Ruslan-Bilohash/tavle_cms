# Bilen CMS

Універсальний **PHP-маркетплейс оголошень авто** від [bilohash.com](https://bilohash.com/) — Норвегія, Європа та Україна. Автомобілі, номерні знаки, спецтехніка, багатомовне SEO, SQLite та повна адмін-панель.

## Live demo

| Сторінка | URL |
|----------|-----|
| Каталог | https://bilohash.com/tavle/site/ |
| Номерні знаки | https://bilohash.com/tavle/site/plates/ |
| Спецтехніка | https://bilohash.com/tavle/site/special/ |
| Додати оголошення | https://bilohash.com/tavle/site/add.php |
| Адмін-панель | https://bilohash.com/tavle/site/admin/ |
| Реліз продукту | https://bilohash.com/news/tavle.html |

**Адмін:** `admin` / `admin123`

Інші мови: [readme.md](readme.md) · [readme-no.md](readme-no.md) · [readme-ru.md](readme-ru.md)

## Мови

Українська, англійська, російська, норвезька — публічний сайт, адмінка, SEO, Schema.org.

## Основні можливості

### Публічний сайт
- **Оголошення авто** — фільтри, AJAX-каталог, карусель фото
- **Номерні знаки** та **спецтехніка** — окремі розділи
- **Майстер додавання** — 4 кроки: контакти → дані → фото → перегляд
- **Чернетки** — зберегти без публікації
- **Масове завантаження фото** — до 20 зображень, drag & drop
- **Акаунти користувачів** — автоматично при першому оголошенні
- **Мої оголошення** — редагування власних оголошень

### SEO
- `hreflang`, canonical, Open Graph, Schema.org (`Car`, `WebSite`)
- SEO-лендінги марка/модель, регіональні сторінки
- `llms.txt` для AI-агентів

### Адмін-панель
- Dashboard зі статистикою та чернетками
- Оголошення, бренди, моделі, користувачі, новини
- Навігація та налаштування (SEO, валюта, cookie)
- Завантаження фото в оголошеннях
- 4 мови інтерфейсу адмінки

## Технології

PHP 8+, SQLite, Bootstrap 5 (адмінка), власний CSS/JS (фронт).

## Встановлення

```text
/tavle/site/
  data/            ← права на запис
  uploads/cars/    ← права на запис
  install.php      ← перевірка (видалити після деплою!)
```

1. Завантажити файли на сервер.
2. `chmod 755` для `data/` та `uploads/cars/`.
3. Відкрити `install.php` — БД створиться автоматично.
4. Увійти в `/admin/`, змінити пароль admin.
5. Видалити `install.php`.
6. `APP_ENV = production` у `config.php`.

## Налаштування

```php
define('SITE_URL', 'https://bilohash.com/tavle/site');
define('BASE_PATH', '/tavle/site');
```

## Публікація оголошення

1. `add.php` → контакти (email створює акаунт).
2. Дані авто + фото.
3. **Опублікувати** або **Зберегти чернетку**.
4. Редагування: `my-listings.php` або `add.php?edit={id}`.

## Замовити доопрацювання

Vipps/Stripe, імпорт XML, підписки дилерів, кастомний дизайн:

**https://bilohash.com/website/**

## Автор

**BILOHASH** — PHP CMS та веб-розробка, Drammen, Norway  
https://bilohash.com/