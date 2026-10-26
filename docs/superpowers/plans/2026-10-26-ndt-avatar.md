# NDT Avatar Implementation Plan

**Goal:** Ship a bilingual, databaseless PHP avatar API and Next.js playground under NDT-Avatar.
**Architecture:** Pure PHP renderer with GD raster encoding, a strict options boundary and HTTP controller. Next.js static export served alongside PHP through Nginx; Node required only for build. File cache is optional and bounded by maintenance.
**Tech stack:** PHP >=8.4, GD/FreeType, intl, mbstring; Next.js, React, TypeScript; Nginx and Docker.
**Spec:** ../specs/2026-10-26-ndt-avatar-design.md

## Global constraints
- No database, accounts, tracking, or secret material in repository.
- English and Vietnamese interface; owner nguyenduytan; repo NDT-Avatar.
- Preserve applicable license notices in LICENSE; no source discussion in README.
- No fabricated benchmarks, usage counts, screenshots or test claims.
- Static export is an intentional optimization of the approved architecture: no persistent Node runtime needed on VPS.
- Image concept delivery failed; use the actual new.id.vn reference and inspect the implemented UI directly. Do not claim concept comparison.

## 1. API
- [ ] Add executable tests in api/tests/run.php for Vietnamese initials, NFC, array rejection, ranges, deterministic colors, SVG injection and raster transparency.
- [ ] Run php -d extension=gd api/tests/run.php and observe missing bootstrap failure.
- [ ] Implement Options::from(array), Renderer::render(Options), Cache::remember(key, callback), App::run().
- [ ] Expose /api, /api/v1/avatar, /api/v1/meta, /health. GET/HEAD/OPTIONS only; errors JSON; ETag/304; optional file cache; Nginx rate limit.
- [ ] Run tests; add HTTP smoke assertions for errors, HEAD, cache, CORS and unknown routes.

## 2. Website
- [ ] Create web/app with static English/Vietnamese routes and responsive dark UI from new.id.vn tokens.
- [ ] Implement Playground, Controls, Preview, Snippets and documentation; API drives preview and downloads.
- [ ] Use URL state and reusable options contract; copy URL/cURL/HTML/Markdown; presets, randomization, reset, accessible errors.
- [ ] Install locked dependencies and run typecheck, lint, build; inspect browser desktop/mobile and real API interactions.

## 3. Operations and documentation
- [ ] Multi-stage Docker build exports Next.js and installs PHP extensions; Compose separates PHP-FPM and Nginx.
- [ ] Bounded FastCGI cache, request rate limit, healthcheck, loopback default port; deployment and HTTPS instructions.
- [ ] README/README.vi, OpenAPI, CI, changelog, license, contribution/security and release packaging.
- [ ] Generate dated repository stats from code, report environment/version and test evidence.

## 4. Publication
- [ ] Initialize Git, commit source after tests, inspect tracked files for secrets and build artifacts.
- [ ] Verify authenticated GitHub identity; create public nguyenduytan/NDT-Avatar only if absent; no overwrite/force push.
- [ ] Push main, verify CI, create v1.0.0 with notes and source artifacts only once verification passes.
- [ ] If authentication or Docker is unavailable, report exact limits and preserve a concrete reproducible handoff.
