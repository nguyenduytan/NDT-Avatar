# NDT Avatar Product Design Specification

## Product goal

Build a production-ready avatar generation service inspired by the current ui-avatars behavior, with a stronger API contract, a polished bilingual Next.js playground, databaseless operation, PHP 8.4 compatibility, Docker/VPS deployment, and GitHub release readiness.

## Repository and identity

- Repository name: `NDT-Avatar`
- GitHub owner: `nguyenduytan`
- Product brand: NDT Avatar
- Website: https://nguyenduytan.com
- Footer copy: Made with ❤️ by Tony Nguyễn
- Documentation language: English and Vietnamese sections where useful.
- README must not mention upstream/source attribution.

## Architecture

The repository is a monorepo:

- `api/`: PHP 8.4 stateless HTTP API.
- `web/`: Next.js App Router website, playground, and API documentation.
- `docker/`: production Nginx/PHP configuration.
- `docs/`: deployment, API, and release documentation.
- `.github/workflows/`: CI and release automation.
- Root Docker Compose orchestrates API and web services.

The API generates SVG or PNG responses without a database. Generated PNG files may use filesystem cache with deterministic cache keys; SVG remains fully stateless. The web application does not require a database and uses client state for the playground.

## API requirements

### Compatibility endpoint

Expose a compatibility endpoint at `/api` supporting familiar parameters:

- `name`
- `size`, `width`, `height`
- `background`
- `color`
- `length`
- `uppercase`
- `bold`
- `rounded`
- `format=png|svg`
- `font-size`
- `no-cache`

### Versioned endpoint

Expose `/api/v1/avatar` with the same rendering capabilities plus consistent validation and JSON errors.

### Metadata endpoints

- `GET /health`: liveness and version.
- `GET /api/v1/meta`: supported formats, limits, defaults, and API version.
- `OPTIONS`: CORS preflight.

### Response behavior

- SVG responses use `image/svg+xml`.
- PNG responses use `image/png`.
- JSON error responses use a stable shape:
  `{ "error": { "code": "...", "message": "...", "details": {} } }`.
- Add CORS headers, cache headers, security headers, and a service version header.
- Validate dimensions, colors, name length, format, and booleans.
- Sanitize all text and color input before SVG output.
- Keep the service compatible with PHP 8.4 and Composer optimized autoloading.

## Web requirements

The Next.js app includes:

- Hero with clear product value and live avatar preview.
- Playground form with name, size, colors, shape, initials length, typography, format, and URL controls.
- Copy controls for URL, HTML, Markdown, cURL, and JSON.
- Download controls for SVG and PNG.
- Preset examples.
- API documentation with endpoint, parameters, examples, response behavior, and errors.
- VPS/Docker quick start.
- English/Vietnamese locale switch.
- Responsive layouts for desktop and mobile.
- Accessible keyboard and screen-reader behavior.
- No server-side database.

## Visual system

- Dark ink background with cool blue surfaces.
- NDT red accent for primary actions and brand details.
- High-contrast text and readable monospace code blocks.
- Rounded but restrained surfaces, with clear hierarchy and generous spacing.
- Product UI should feel like a developer tool rather than a generic marketing page.
- Use a large live avatar preview as the main visual anchor.
- Use Lucide or small custom SVG icons consistently.
- Respect reduced-motion preferences.

## Deployment

- PHP 8.4 API container.
- Node/Next.js web container.
- Nginx reverse proxy configuration.
- Docker Compose for local and VPS use.
- `.env.example` with public API URL and web port.
- Healthchecks.
- Non-root runtime where practical.
- GitHub Actions for install, lint, typecheck, build, and API smoke checks.
- Release notes and tag `v1.0.0`.

## README requirements

README must include:

- Centered project title and badges.
- Centered product summary.
- Update date in stats section.
- Feature overview.
- Screenshots or preview section.
- API quick start.
- Parameter reference table.
- cURL, HTML, Markdown, JavaScript examples.
- Next.js development instructions.
- PHP/Docker/VPS deployment instructions.
- Environment variables.
- Healthcheck and troubleshooting.
- Contributing and release workflow.
- License.
- Footer links to website and GitHub.
- Copyright line with the exact requested wording.
- No note describing the project as based on another source.

## Quality gates

- API unit tests for initials, validation, sanitization, formats, and cache keys.
- API smoke test against running service.
- Web lint, typecheck, and production build.
- Browser verification of desktop and mobile layouts.
- Verify live preview, locale switch, copy controls, download controls, and API examples.
- Verify Docker Compose starts both services.
- Verify Git status is clean before release.

