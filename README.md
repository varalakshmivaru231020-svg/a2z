# AKS Global Maintenance Facility Management Services — website

A mobile-responsive Laravel 12 website with an admin panel for **AKS Global Maintenance Facility Management Services**
(facility management, maintenance, BWSSB projects and field services — Bangalore & Kochi).
Logo files are in `public/img/`: `logo.png` (full logo, header), `logo-footer.png` (light-on-dark version for the dark footer),
`logo-mark.png` (emblem only), plus the favicon, app icons and `og-default.jpg` share image. Logo URLs carry a `?v=` version
(`App\Support\Assets`), so replacing a file is picked up by browsers straight away.
Colours follow the logo: royal blue and orange (CSS variables at the top of `public/css/site.css`).

| Public site | Admin panel (`/admin`) |
|---|---|
| **Home** – intro, service highlights, categories, clients, gallery preview, enquiry form | Secure login (throttled, session based) |
| **About Us** – profile, vision & mission, leadership, clients | Add / edit / delete **services** (photo, features, SEO fields) |
| | Add / edit / delete **leadership team** (photo, bio, display order) |
| **Services** – grouped listing + a detail page per service | Post, edit, **close / reopen** and delete **job openings** |
| **Recruitment** – open jobs + detail page + online application with resume | View **applications**, download **resumes**, set status, notes, export **CSV** |
| **Gallery** – photo grid with lightbox | Upload (several at once) and manage **gallery** photos |
| **Contact Us** – details, 3 offices, switchable Google map, enquiry form | Read **contact enquiries** (unread badge) |
| | Edit **SEO title/description** of every static page |

## Quick start (local)

Requires PHP 8.2+ with the `gd`, `fileinfo`, `mbstring`, `zip` and `pdo_sqlite` (or `pdo_mysql`) extensions, and Composer.
There is **no Node/npm build step** — the CSS/JS are plain files in `public/`.

```bash
composer install
cp .env.example .env            # then set ADMIN_PASSWORD (see below)
php artisan key:generate
touch database/database.sqlite  # SQLite by default; use MySQL by editing DB_* in .env
php artisan migrate --seed      # creates tables, the admin user and the 22 brochure services
php artisan serve               # http://127.0.0.1:8000  ·  admin: /admin
```

**Admin login:** `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env` are used by `php artisan db:seed`.
If `ADMIN_PASSWORD` is empty a random one is generated and printed once. To change the password later,
set a new `ADMIN_PASSWORD` and run `php artisan db:seed --class=AdminUserSeeder`.
**Use a strong password before going live.**

Want sample jobs, photos, an enquiry and applications to click around with? (local only — never on the live site)

```bash
php artisan db:seed --class=DemoContentSeeder
```

## Editing content

| What | Where |
|---|---|
| Services, jobs, gallery, leadership team, SEO of static pages | Admin panel |
| Phone, email, WhatsApp, the 3 offices, clients, nav, service categories | `config/site.php` |
| Default SEO title/description for each static page | `config/seo.php` (admin overrides win) |
| Page layouts / wording | `resources/views/pages`, `services`, `recruitment` |
| Colours & styling | `public/css/site.css` (variables at the top), `public/css/admin.css` |

## SEO (every page)

* Unique `<title>` and meta description, canonical URL, robots, Open Graph and Twitter cards on every page (`app/Support/Seo.php`, `resources/views/partials/seo.blade.php`).
* Structured data (JSON-LD): Organization + WebSite (home), AboutPage, CollectionPage (services / recruitment), **Service** (each service), **JobPosting** (each open job — Google for Jobs), ImageGallery, ContactPage, and BreadcrumbList on every inner page.
* `/sitemap.xml` (open jobs and live services only) and `/robots.txt` are generated automatically; closed jobs, hidden services, the admin and 404s are `noindex`.
* Services and jobs have their own **meta title / description / URL slug** fields; the six static pages are edited under **SEO pages** in the admin (with live character counters).
* One `<h1>` per page, alt text on all images, explicit image sizes, lazy loading, breadcrumb navigation, `lang="en-IN"`.

After launch, submit `https://<your-domain>/sitemap.xml` in Google Search Console.

## Security notes

* Resumes are stored **privately** (`storage/app/private/resumes`, random names) and can only be downloaded by a logged-in admin. They are never in the public web root.
* Resume uploads are checked by *contents*, not just file name (PDF / DOC / DOCX, 5 MB). Photos are re-encoded with GD (strips metadata; SVG is refused).
* Forms use CSRF, a honeypot field and rate limiting; admin login is throttled; CSV export neutralises spreadsheet formulas; admin pages send `noindex` and `no-store`.
* Photos are saved in `public/uploads` (no `storage:link` needed on shared hosting) — make sure that folder is writable.

## Deploying

1. Point the domain's document root at the **`public/`** folder.
2. Set `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`, real `DB_*` (MySQL), a strong `ADMIN_PASSWORD`, and mail settings if you add notifications.
3. `composer install --no-dev --optimize-autoloader`, `php artisan key:generate`, `php artisan migrate --seed --force`
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. Make `storage/` and `public/uploads/` writable by the web server.

## Tests

```bash
php artisan test        # 51 tests: pages + SEO output, forms, uploads, resume privacy, admin auth & CRUD, CSV export
```

## Before you launch — content still carried over from the earlier brochure

The site was first built from a different company's brochure (Josha Infinity). The **name and logo are now AKS Global Maintenance**,
but this content has *not* been replaced yet and must be checked or swapped for AKS's own details (all in `config/site.php`
unless noted):

* **Phone, email and WhatsApp** are still the earlier company's. The corporate office (Varthur Road, Bengaluru) has been updated;
  the Horamavu (Bangalore) and Edappally (Kochi) branch addresses — including the "Chttupabukara" spelling copied as printed —
  are still carried over. The contact email is also the default admin login and mail "from" address (`.env`).
* **Established 2023**, the "7+ yrs leadership experience" stat and the tagline / slogan ("We care your needs", "Have an exciting comfort always…").
* **Leadership** (names, roles, bios and the Founder & CEO's photo) — now editable in **Admin → Leadership**; the brochure names
  above were seeded as a starting point, so review/replace them there. The **client list** is still just names in `config/site.php`,
  shown as a scrolling text strip on the Home and About pages.
* **Vision, mission and all 22 service descriptions are draft copy** — edit services in the admin panel, vision/mission in `resources/views/pages/about.blade.php`.
* `md-prasad-ashok.jpg`, `office.jpg` and `crew.jpg` in `public/img/` are photos from that brochure; replace with AKS's own.
