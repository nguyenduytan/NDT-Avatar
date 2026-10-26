# 🔒 Security

Report suspected vulnerabilities privately through GitHub Security Advisories when enabled, or contact the maintainer through https://nguyenduytan.com. Do not include credentials or personal data in public issues.

The service accepts bounded scalar query parameters and generates images locally. It does not fetch remote URLs or accept uploads. Run it behind HTTPS and rate limiting. Keep PHP, Nginx, Node build tooling and OS packages updated.

Names in query strings may appear in browser history, proxy/CDN logs and caches. Avoid confidential data in names or seeds. Optional filesystem caching requires expiry maintenance. See the deployment guide for trusted proxy and cache settings.

Version 1.x is the initial supported release line. Security fixes should be accompanied by regression tests.
