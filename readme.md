# Bilen CMS

Universal **PHP car classifieds marketplace** from [bilohash.com](https://bilohash.com/) — Norway, Europe and Ukraine. Cars, license plates, special equipment, multilingual SEO, SQLite database and full admin panel.

## Live demo

| Page | URL |
|------|-----|
| Marketplace | https://bilohash.com/tavle/site/ |
| Cars catalog | https://bilohash.com/tavle/site/ |
| License plates | https://bilohash.com/tavle/site/plates/ |
| Special equipment | https://bilohash.com/tavle/site/special/ |
| Add listing (wizard) | https://bilohash.com/tavle/site/add.php |
| Admin panel | https://bilohash.com/tavle/site/admin/ |
| Product / release | https://bilohash.com/news/tavle.html |

**Admin:** `admin` / `admin123`  
**Demo users:** created automatically when posting a public listing (email-based account).

## Languages

English, Ukrainian, Russian, Norwegian — public site, admin panel (partial), contact forms, SEO meta and Schema.org.

Other language files: [readme-uk.md](readme-uk.md) · [readme-no.md](readme-no.md) · [readme-ru.md](readme-ru.md)

## Key features

### Public frontend
- **Car listings** — filters (brand, model, price, year, mileage, body, fuel, region), AJAX catalog, photo carousel on cards
- **License plates** — separate catalog and SEO landings
- **Special equipment** — trucks, tractors, construction machinery demo listings
- **Sticky filters** — Buy / Leasing toolbar, mobile-friendly search spoiler
- **Add listing wizard** — 4 steps: contact → details → bulk photo upload → preview
- **Draft listings** — save without publishing; edit later in “My listings”
- **Bulk photo upload** — up to 20 images (JPG, PNG, WebP, 5 MB each), drag & drop, live preview
- **User accounts** — auto-created on first listing (email + generated password)
- **My listings** — edit own ads, draft badges
- **GDPR cookie consent** — configurable banner
- **Ecosystem footer** — links to other BILOHASH products

### SEO & performance
- Multilingual `hreflang`, canonical URLs, Open Graph
- Schema.org: `Car`, `WebSite`, `Organization`, `SoftwareApplication`, breadcrumbs
- Brand/model SEO landings (`/bmw/`, `/bmw/x5/`)
- Regional pages (Norway cities, EV, import/export, dealership)
- `llms.txt` for AI crawlers
- Pre-built Tailwind CSS, lazy images, srcset on cards

### Admin panel (`/admin/`)
- **Dashboard** — stats (active listings, drafts, brands, views), recent listings, quick links
- **Listings** — cars, plates, special equipment; photo upload; activate/deactivate
- **Brands & models** — CRUD
- **Users** — admin / dealer / user roles
- **News** — multilingual articles, draft/publish
- **Navigation** — top bar and header menu editor (JSON in settings)
- **Settings** — site name, SEO titles/descriptions per language, currency, cookies text
- **Admin i18n** — EN / UK / NO / RU switcher in top bar

## Technology

| Component | Details |
|-----------|---------|
| PHP | 8.0+ (8.3 recommended) |
| Database | SQLite (`data/bilen.sqlite`) — no MySQL required |
| Frontend | Custom CSS + minimal JS (filters, sliders, listing wizard) |
| Admin | Bootstrap 5.3 + Bootstrap Icons |
| Uploads | `uploads/cars/` (writable) |

## Installation

```text
/tavle/site/
  config.php          ← SITE_URL, BASE_PATH, APP_ENV
  data/               ← writable (SQLite DB)
  uploads/cars/       ← writable (listing photos)
  database/installer.php
  install.php         ← health check (delete after deploy!)
  admin/
  languages/
  assets/
```

1. Upload files to the server (e.g. `public_html/tavle/site/`).
2. Set `chmod 755` (or `775`) on `data/` and `uploads/cars/`.
3. Open `install.php` in the browser — DB is created automatically.
4. Log in to `/admin/` with default credentials and change the admin password.
5. **Delete `install.php`** after successful deployment.
6. Set `APP_ENV` to `production` in `config.php`.

### Requirements
- PHP 8+ with `pdo_sqlite`, `gd` or `fileinfo` (for image MIME check)
- HTTPS recommended (secure session cookies in production)
- Apache `mod_rewrite` optional (clean URLs via `.htaccess`)

## Configuration (`config.php`)

```php
define('SITE_URL', 'https://bilohash.com/tavle/site');
define('BASE_PATH', '/tavle/site');
define('APP_ENV', 'production');
define('DEFAULT_LANG', 'en');
define('AVAILABLE_LANGS', ['uk', 'en', 'ru', 'no']);
```

## Default accounts

| Role | Login | Password |
|------|-------|----------|
| Admin | `admin` | `admin123` |

Change immediately after installation.

## Public listing flow

1. User opens **Add listing** (`add.php`).
2. Fills contact (name, email, phone) — account is created or matched by email.
3. Enters car details, uploads photos (optional).
4. **Publish** → listing is live (`is_active=1`) or **Save draft** → hidden (`is_draft=1`).
5. User can edit via `my-listings.php` or `add.php?edit={id}`.

## Photo storage

- Files: `uploads/cars/{carId}_{random}.jpg`
- DB table: `car_images` (filename, sort_order, is_main)
- Demo listings may use Unsplash URLs or placeholders
- API: `api/listing-images.php` (upload / delete / set main) for logged-in owners

## Database migrations

Migrations run automatically on each request via `DatabaseInstaller::migrate()`:
- `listing_type` (car / plate / special)
- `user_id` on cars (public ownership)
- `is_draft` for draft listings

## SEO selling points

- 4 languages with separate meta titles and descriptions
- Norway & Europe positioning (Drammen, Oslo, Bergen, etc.)
- Structured data for vehicles and organization
- Fast catalog with filter AJAX (`api/cars.php`)
- Agent-friendly `llms.txt`

## Order / customization

Custom themes, payment gateways, Vipps/Stripe, dealer subscriptions, XML import, mobile app API:

**https://bilohash.com/website/**

## Author

**BILOHASH** — PHP CMS & web development, Drammen, Norway  
https://bilohash.com/

## License

Commercial script. Demo for evaluation. Contact BILOHASH for production license.