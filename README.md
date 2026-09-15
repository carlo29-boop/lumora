# LUMORA — Premium Real Estate Landing Page

A modern, editorial real-estate landing page built with WordPress and Elementor Free, focused on refined visual design, responsive layouts, accessibility, and maintainable implementation.

## Stack (verified local)

- WordPress 7.1 · PHP 8.2.29 · nginx 1.26.1 · MySQL 8.4.0 (Local)
- Parent theme: Hello Elementor 3.5.1 (unmodified)
- Child theme: `lumora-child` 1.0.0 (this repo — versioned brand CSS only)
- Page builder: Elementor Free 4.2.4, Containers only, Canvas template
- Site URL (Local): http://localhost:10003/ · http://lumora.local (if mapped)

## What is tracked in git

Only project files — never core, secrets, or generated content:

- `app/public/wp-content/themes/lumora-child/` (style.css, functions.php, assets/css/lumora.css)
- `README.md`, `.gitignore`, `docs/` (if present)

Excluded: `wp-config.php`, passwords/salts, `*.sql`/database dumps, `wp-content/uploads/`,
`wp-content/plugins/`, WordPress core (`wp-admin/`, `wp-includes/`, root `wp-*.php`),
Local `conf/`/`logs/`, Elementor generated CSS (`uploads/elementor/css/`), caches, `node_modules`.

## Structure

- Page ID 6 — title `LUMORA`, slug `lumora-home`, template `elementor_canvas`, set as homepage
  (`show_on_front=page`, `page_on_front=6`). Content lives in `_elementor_data` via the
  Elementor Document API, so the page stays fully editable in Elementor Free.
- Header + footer are built **in-page** (first/last Containers). The theme header/footer is
  bypassed on this Canvas page, which also removes the default Hello “All rights reserved”
  line from this page (the visible footer line is our own `© 2026 LUMORA` container).
- Design tokens: ink `#1C1B18`, secondary `#716D64`, text `#3F3C36`, accent `#A48662`,
  background `#F4F1EA`, white `#FFFFFF`. Headings `Cormorant Garamond`, body/nav `DM Sans`
  (Google Fonts, system fallbacks).

## Run locally

1. Open the site in Local (PHP 8.2.29, MySQL socket `.../run/<id>/mysql/mysqld.sock`).
2. Frontend: http://localhost:10003/ (homepage = LUMORA).
3. Edit: WP Admin → Pages → LUMORA → **Edit with Elementor**
   (or `/wp-admin/post.php?post=6&action=elementor`).
4. After programmatic changes: Elementor → Tools → Regenerate Files (or `wp elementor flush-css`).

## Rebuilding the page programmatically

The page was populated via `Document::save(['elements'=>..., 'settings'=>...])`
(see build script used during development, not committed). Never write `_elementor_data`
with raw SQL — always go through `$doc->save()` so Elementor rebuilds `_elementor_version`,
`_elementor_css` cache, and revisions. Backup `wp_posts`/`wp_postmeta` before re-running.

## Images

Architectural photography from Unsplash (freely usable, Unsplash License), downloaded to the
Media Library (IDs 8–15) — not committed to git. No hotlinking in production markup
(all `srcset` URLs are local).

## Responsive / QA

Containers + class-driven grid (`.lumora-grid-3/.lumora-grid-2`) with breakpoints at
1440 / 1200 / 1024 / 768 / 390 / 375, `overflow-x: clip` guard, focus-visible states,
`prefers-reduced-motion` support. Verify visually in Elementor responsive mode + browser
devtools before review.
