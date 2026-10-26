# 🚀 Deployment / Triển khai VPS

## Docker Compose (recommended)

Use a Linux VPS with Docker Engine and Compose v2. Start with 1 GB RAM for runtime; allow more memory for the Next.js build. These are starting allocations, not load-tested capacity guarantees.

```bash
git clone https://github.com/nguyenduytan/NDT-Avatar.git
cd NDT-Avatar
cp .env.example .env
docker compose up -d --build --wait
curl --fail http://127.0.0.1:8080/health
```

The API runs PHP 8.4-FPM with four workers and a 512 MB container memory ceiling. Nginx serves the static Next.js export and forwards only API routes. There is no production Node process or database. Fonts are bundled. The PHP filesystem is read-only, with writable temporary memory.

Mặc định chỉ mở cổng trên `127.0.0.1`. Để cung cấp API public, trỏ DNS về VPS, mở 80/443 cho reverse proxy và bật HTTPS. Không mở trực tiếp cổng PHP-FPM 9000 ra Internet.

## HTTPS with Caddy on the host

Install Caddy from its official repository. Point your domain at the VPS, then use:

```caddyfile
avatars.example.com {
    reverse_proxy 127.0.0.1:8080
}
```

Replace the example hostname. Validate your Caddy configuration before reloading. If Caddy is itself inside Docker, `127.0.0.1` refers to its container; join the Compose network and proxy to `gateway:8080` instead.

The included rate limit sees the directly connected IP. Behind a reverse proxy configure Nginx `set_real_ip_from` for **only** the trusted proxy address/network and `real_ip_header X-Forwarded-For`; never trust arbitrary Internet-supplied forwarding headers. Without this, all requests may share the proxy's limit.

## PHP-only or shared hosting

Upload `api/`, including `fonts/`, outside the public web directory. Set the document root to `api/public` and route API requests to `index.php`. PHP needs GD with FreeType/WebP, intl, mbstring. No Composer installation is required.

Build `web` locally with `npm ci && npm run build`; upload the contents of `web/out/` into your site's public directory. Configure the host to send `/api/*` and `/health` to the PHP front controller, before the static-site fallback. Keep source, `.env`, fonts and caches outside the public document root. Apache hosts can use rewrite rules for this routing; the supplied configuration targets Nginx.

## Caching and maintenance

- Compose uses Nginx FastCGI cache: 256 MB maximum, inactive entries expire after 24 hours.
- PHP file cache is disabled by default to avoid maintaining a second cache layer.
- To enable PHP caching outside Docker, create a writable directory outside the document root and set `CACHE_DIR` in the PHP-FPM environment.
- Schedule `php api/bin/cleanup.php` hourly with the same `CACHE_DIR`/`CACHE_TTL` environment. File cache is TTL-managed and has no hard byte quota; use filesystem quotas or prefer bounded Nginx cache for public endpoints.
- `no-cache` bypasses both layers; it does not bypass Nginx rate limiting.
- Nginx limits API requests to 20 per second per client IP with a burst of 40. Tune `docker/nginx.conf` after measuring your deployment.
- API request access logs are disabled by default because query strings can contain names. Upstream proxies/CDNs may still log URLs.
- Do not enable credentials with wildcard CORS. This service has no cookies/authentication.

## Updates and rollback

```bash
git fetch --tags
git checkout <reviewed-release-tag>
docker compose up -d --build --wait
```

Pin a reviewed tag rather than deploying arbitrary main commits. To roll back, check out the previous tag and rebuild. Preserve `.env`. Cache can be discarded without losing user records because none exist. No database migrations are required.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Health 503 | GD, intl, mbstring and both bundled font files |
| PNG/WebP 500 | GD FreeType/WebP support, font readability, memory limit |
| SVG works, raster fails | PHP extensions or missing fonts |
| Website preview does not load | API routing; build-time `NEXT_PUBLIC_API_BASE`; browser console |
| 429 for many users | Trusted proxy real-IP setup and rate-limit policy |
| Stale avatar | ETag/cache headers; use `no-cache` during debugging |
| Wrong glyph or square | Font coverage; select an appropriate licensed font in `FONT_DIR` |

Inspect `docker compose ps` and `docker compose logs --tail=100`. Run `API_URL=https://your-domain node scripts/smoke.mjs` to verify the deployed API. CORS smoke assertion expects the default `*`; adjust it if using a specific origin.

## Verification boundaries

Local development used PHP 8.5.1 on Windows. GitHub Actions run 40282776355 passed PHP 8.4/8.5, web and Linux container verification. The local machine did not have Docker installed at implementation time.
