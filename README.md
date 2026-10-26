<div align="center">

# 🎨 NDT Avatar

### Small images. Big identity.

**A databaseless avatar API and bilingual Next.js playground.**

[![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Next.js](https://img.shields.io/badge/Next.js-16-black?logo=nextdotjs)](https://nextjs.org/)
[![CI](https://github.com/nguyenduytan/NDT-Avatar/actions/workflows/ci.yml/badge.svg)](https://github.com/nguyenduytan/NDT-Avatar/actions/workflows/ci.yml)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)
[![Release](https://img.shields.io/github/v/release/nguyenduytan/NDT-Avatar)](https://github.com/nguyenduytan/NDT-Avatar/releases)
[![Stars](https://img.shields.io/github/stars/nguyenduytan/NDT-Avatar?style=social)](https://github.com/nguyenduytan/NDT-Avatar)

[English](README.md) · [Tiếng Việt](README.vi.md) · [Deployment](docs/DEPLOYMENT.md) · [API specification](web/public/openapi.json) · [Website](https://nguyenduytan.com)

</div>

<h2 align="center">✨ Features</h2>

- Unicode-aware initials with Vietnamese support, NFC normalization and grapheme extraction.
- Initials, symmetric identicons and abstract rings, with deterministic seed-based colors.
- SVG, PNG, WebP and JSON output; 16–2048 pixels; independent width and height.
- Custom colors, automatic text contrast, transparency, gradients and rounded corners.
- English and Vietnamese playground, six palettes, live preview, snippet copying and downloads.
- Batch URL export for up to 100 names, generated locally in the browser.
- Strict input validation, XML escaping, CORS, ETag, conditional GET and HEAD.
- Optional filesystem cache; production Nginx cache capped at 256 MB and per-IP rate limiting.
- Static Next.js export: **no Node runtime or database required on the production VPS**.
- Bundled Noto Sans fonts, Docker Compose, healthchecks and CI for PHP 8.4/8.5.

<h2 align="center">🚀 Quick start</h2>

```bash
git clone https://github.com/nguyenduytan/NDT-Avatar.git
cd NDT-Avatar
cp .env.example .env
docker compose up -d --build --wait
```

Open **http://localhost:8080**; Vietnamese: **http://localhost:8080/vi/**.

The default bind address is loopback. For a public VPS, place an HTTPS reverse proxy in front; see [deployment instructions](docs/DEPLOYMENT.md). Docker builds need network access to the official package registries.

```bash
curl --fail 'http://localhost:8080/api/v1/avatar?name=Tony+Nguyen&format=png&background=e95365&color=fff&rounded=true' -o avatar.png
curl --fail 'http://localhost:8080/health'
```

<h2 align="center">🔌 API reference</h2>

| Route | Purpose |
| --- | --- |
| `/api/v1/avatar` | Generate an avatar |
| `/api/` or `/api/index.php` | Query-parameter compatibility aliases |
| `/api/v1/meta` | Supported formats, styles, limits and defaults |
| `/health` | Runtime readiness and version; 503 when required extensions/fonts are missing |
| `/openapi.json` | Machine-readable API description on the website |

All API routes accept GET, HEAD and OPTIONS. No API key is required.

| Parameter | Default | Values / behavior |
| --- | --- | --- |
| `name` | `John Doe` | UTF-8 name, up to 128 characters; blank becomes `?` |
| `size` | `64` | Integer 16–2048 |
| `width`, `height` | `size` | Override each axis, integer 16–2048 |
| `background` | `ddd` | 3/6-digit hex, `random` (deterministic), `transparent` |
| `color` | `222` | 3/6-digit hex, or `auto` for contrast against the base background |
| `format` | `png` | `svg`, `png`, `webp`, `json`; Accept SVG negotiation when omitted |
| `style` | `initials` | `initials`, `identicon`, `rings` |
| `length` | `2` | 1–8; for 2, use first and last word; one word uses its first graphemes |
| `font-size` | `0.4375` | Relative font size, 0.1–1 |
| `bold` | `false` | Boolean |
| `uppercase` | `true` | Boolean |
| `radius` | `0` | 0–50 percent of the shorter axis |
| `rounded` | `false` | Overrides radius to 50%; circle for square images, capsule for rectangles |
| `gradient` | none | Optional second hex background color, top-to-bottom |
| `seed` | `name` | Seed for color/pattern, at most 2048 bytes |
| `download` | `0` | `1` returns an attachment header |
| `no-cache` | absent | Presence disables application and HTTP cache |

Use URL encoding for names and `#` colors. Boolean values include `true`, `false`, `1`, `0`, `yes`, `no`, `on`, `off`. Array-valued parameters and invalid values return 400. Unknown scalar query parameters are ignored. Positional path parameters are not supported. Raster output uses bundled Noto Sans; SVG uses the viewer's available fonts. Raster text shrinks to fit when necessary. Emoji and scripts outside the font's coverage are not guaranteed.

**HTTP caching:** successful images have a strong ETag and `Cache-Control: public, max-age=86400, stale-while-revalidate=3600`. Send `If-None-Match` for a bodyless 304. `X-NDT-Cache` reports application cache status; `X-NDT-Edge-Cache` reports Nginx cache status when present.

**Errors:** 400 invalid parameter, 404 unknown endpoint, 405 unsupported method, 429 Nginx rate limit, 500 rendering error, 503 degraded health. Error responses are not cached.

```json
{"error":{"code":"invalid_parameter","message":"size must be an integer between 16 and 2048","details":{}}}
```

JSON output includes `version`, `initials`, `width`, `height`, `background`, `color`, `style` and the escaped SVG document as a string.

<h2 align="center">💻 Integration examples</h2>

```html
<img src="https://avatars.example.com/api/v1/avatar?name=Tony+Nguyen&amp;format=svg"
     width="64" height="64" alt="Tony Nguyen" />
```

```markdown
![Tony Nguyen](https://avatars.example.com/api/v1/avatar?name=Tony+Nguyen&format=svg)
```

```js
const url = new URL('/api/v1/avatar', 'https://avatars.example.com');
url.search = new URLSearchParams({
  name: 'Tony Nguyễn', background: 'random', color: 'auto', format: 'png'
});
const response = await fetch(url);
if (!response.ok) throw new Error(`Avatar failed: ${response.status}`);
const image = await response.blob();
```

`avatars.example.com` is an example hostname. Replace it with your deployment.

<h2 align="center">🛠️ Development</h2>

Requirements: PHP 8.4+ with GD/FreeType, intl and mbstring; Node.js 22+ (24 recommended); npm. PHP has no third-party runtime packages, so Composer install is optional.

```bash
# Terminal 1 — real API
php -S 127.0.0.1:8091 api/router.php

# Terminal 2 — build and preview static website with same-origin API proxy
cd web
npm ci
npm run build
cd ..
node scripts/preview.mjs
```

Visit http://127.0.0.1:3000. For hot reload, set `NEXT_PUBLIC_API_BASE=http://127.0.0.1:8091/api/v1/avatar` in `web/.env.local`, then run `npm run dev` from `web`. The default build uses `/api/v1/avatar` on the same origin.

On Windows, if GD is installed but disabled, use `php -d extension=gd` for the API and tests.

```bash
php api/tests/run.php
node scripts/smoke.mjs  # API must be running on 8091
cd web
npm run lint
npm run typecheck
npm run build
```

| Variable | Default | Scope |
| --- | --- | --- |
| `BIND_ADDRESS` | `127.0.0.1` | Compose published interface |
| `PORT` | `8080` | Compose HTTP port |
| `CORS_ORIGIN` | `*` | API allowed origin; use a single exact origin if needed |
| `NEXT_PUBLIC_API_BASE` | `/api/v1/avatar` | Next.js build-time API endpoint |
| `FONT_DIR` | `api/fonts` | PHP font directory with Regular/Bold Noto Sans filenames |
| `CACHE_DIR` | empty, disabled | Optional PHP cache directory, outside document root |
| `CACHE_TTL` | `86400` | PHP cache expiry in seconds, minimum 60 |
| `API_URL` | `http://127.0.0.1:8091` | HTTP smoke-test base URL |

<h2 align="center">📊 Project stats</h2>

<div align="center">

**Updated: 2026-10-26** · Source snapshot, not traffic analytics

| Capability | Count |
| --- | ---: |
| Output formats | 4 |
| Avatar styles | 3 |
| Interface languages | 2 |
| Color presets | 6 |
| Core test assertions | 24 |
| HTTP test assertions | 18 |

</div>

These counts describe the implementation and included tests. No throughput or public-usage claims are made. Local checks used PHP 8.5.1 and Node 24; CI is configured to verify PHP 8.4/8.5 and Docker separately.

<h2 align="center">📚 Maintenance</h2>

- [Deployment, HTTPS, caching and troubleshooting](docs/DEPLOYMENT.md)
- [Contributing](CONTRIBUTING.md) · [Security](SECURITY.md) · [Changelog](CHANGELOG.md)
- [MIT license](LICENSE); bundled fonts carry their own [OFL license](api/fonts/OFL.txt).
- Before tagging a release, require passing API, web and container CI jobs. Attach source archives from the tag; do not include `.env`, caches or `node_modules`.

<div align="center">

© 2026 NDT Avatar · Made with ❤️ by [Tony Nguyễn](https://nguyenduytan.com)

[Website](https://nguyenduytan.com) · [GitHub](https://github.com/nguyenduytan/NDT-Avatar)

</div>
