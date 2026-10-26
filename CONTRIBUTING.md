# 🤝 Contributing

Open an issue describing the behavior and reproduction before large changes. Keep the API databaseless, validate inputs at the Options boundary and update both locales for UI changes.

1. Create a focused branch.
2. Add regression tests for behavioral changes.
3. Run `php api/tests/run.php`, HTTP smoke tests, web lint/typecheck/build.
4. Check mobile and desktop with a real PHP API.
5. Update OpenAPI, README and changelog when the API changes.
6. Submit a pull request with the problem, resulting behavior and verification evidence.

Do not commit credentials, `.env`, dependencies, build caches or private logs. Use conventional commit messages. Releases require passing CI, including Docker readiness and smoke checks.
