# Bilen CMS

Universell **PHP bilannonse-plattform** fra [bilohash.com](https://bilohash.com/) — Norge, Europa og Ukraina. Biler, skilt, spesialutstyr, flerspråklig SEO, SQLite og fullt adminpanel.

## Live demo

| Side | URL |
|------|-----|
| Markedsplass | https://bilohash.com/tavle/site/ |
| Bilskilt | https://bilohash.com/tavle/site/plates/ |
| Spesialutstyr | https://bilohash.com/tavle/site/special/ |
| Legg til annonse | https://bilohash.com/tavle/site/add.php |
| Adminpanel | https://bilohash.com/tavle/site/admin/ |
| Produkt / release | https://bilohash.com/news/tavle.html |

**Admin:** `admin` / `admin123`

Andre språk: [readme.md](readme.md) · [readme-uk.md](readme-uk.md) · [readme-ru.md](readme-ru.md)

## Språk

Norsk, engelsk, ukrainsk, russisk — offentlig nettsted, admin, SEO, Schema.org.

## Hovedfunksjoner

### Offentlig nettsted
- **Bilannonser** — filtre, AJAX-katalog, bildekarusell
- **Bilskilt** og **spesialutstyr** — egne seksjoner
- **Annonseveiviser** — 4 steg: kontakt → detaljer → bilder → forhåndsvisning
- **Utkast** — lagre uten publisering
- **Bulk bildeopplasting** — opptil 20 bilder, drag & drop
- **Brukere** — konto opprettes automatisk ved første annonse
- **Mine annonser** — rediger egne annonser

### SEO
- `hreflang`, canonical, Open Graph, Schema.org
- Merke/modell landingssider, norske bysider
- `llms.txt` for AI-crawlere

### Adminpanel
- Dashboard med statistikk og utkast
- Annonser, merker, modeller, brukere, nyheter
- Navigasjon og innstillinger (SEO, valuta, cookies)
- Bildeopplasting i annonser
- 4 språk i admin

## Teknologi

PHP 8+, SQLite, Bootstrap 5 (admin), egen CSS/JS (frontend).

## Installasjon

```text
/tavle/site/
  data/            ← skrivbar
  uploads/cars/    ← skrivbar
  install.php      ← sjekk (slett etter deploy!)
```

1. Last opp filer til server.
2. `chmod 755` på `data/` og `uploads/cars/`.
3. Åpne `install.php` — database opprettes automatisk.
4. Logg inn på `/admin/`, endre admin-passord.
5. Slett `install.php`.
6. Sett `APP_ENV = production` i `config.php`.

## Publisere annonse

1. `add.php` → kontakt (e-post oppretter konto).
2. Bildetaljer + bilder.
3. **Publiser** eller **Lagre utkast**.
4. Rediger via `my-listings.php` eller `add.php?edit={id}`.

## Bestill tilpasning

Vipps/Stripe, XML-import, forhandler-abonnement, eget design:

**https://bilohash.com/website/**

## Utvikler

**BILOHASH** — PHP CMS og webutvikling, Drammen, Norge  
https://bilohash.com/