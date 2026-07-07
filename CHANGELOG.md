# Changelog

All notable changes to `laravel-localization-i18n` will be documented in this file.

## v0.1.0 - 2026-07-07

Initial release.

- `SetLocale` middleware with configurable resolver pipeline (request parameter, session, `Accept-Language`)
- `PUT /locale` switch route (config-gated)
- `translations:generate` — export PHP lang files to i18next JSON, shared `config.json` and typed `resources.d.ts`, with `--check` mode for CI
- `translations:check` — cross-locale key validation with CI-friendly exit codes
- `translations:unused` — heuristic report of unreferenced keys
- `translations:add-locale` — scaffold a new locale from the default one
- Optional runtime translations API with ETag support (disabled by default)
- Publishable React/TypeScript stubs (lazy Vite-glob i18next backend, `useLocale` hook)
- Optional Inertia shared props
