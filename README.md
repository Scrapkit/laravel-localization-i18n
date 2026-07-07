# Laravel Localization i18n

Translation infrastructure for Laravel applications with a React + TypeScript + Inertia frontend.

Instead of maintaining two translation systems by hand, this package treats the **PHP files in `lang/` as the single source of truth** and generates everything the frontend needs:

- i18next-ready JSON files (one lazy-loadable chunk per namespace)
- a shared `config.json` so the frontend never duplicates locale configuration
- a TypeScript module augmentation so `t()` keys are type-checked

On top of that it provides locale detection middleware, key validation for CI, unused-key reporting and one-command scaffolding of new languages.

## Requirements

- PHP 8.3+
- Laravel 11, 12 or 13
- Frontend (optional): React, `i18next`, `react-i18next`, Vite

## Installation

```bash
composer require scrapkit/laravel-localization-i18n
```

The service provider is auto-discovered. Publish what you need:

```bash
# Configuration
php artisan vendor:publish --tag=localization-i18n-config

# Starter translations (lang/it/common.php, lang/en/common.php)
php artisan vendor:publish --tag=localization-i18n-translations

# Frontend stubs (resources/js/i18n/*.ts)
php artisan vendor:publish --tag=localization-i18n-frontend
```

For the frontend:

```bash
npm install i18next react-i18next
```

## Configuration

```php
return [
    'default_locale' => 'it',
    'fallback_locale' => 'en',
    'supported_locales' => ['it', 'en'],

    'request_parameter' => 'locale',
    'session_key' => 'app_locale',

    // Walked in order by the middleware; first supported locale wins.
    'resolvers' => [
        RequestParameterResolver::class,
        SessionResolver::class,
        AcceptLanguageResolver::class,
    ],

    'routes' => ['switch_enabled' => true, 'middleware' => ['web']],
    'api' => ['enabled' => false, 'prefix' => 'api/translations', 'middleware' => ['api']],

    'frontend' => [
        'output_path' => resource_path('js/locales'),
        'namespaces' => null,   // null = every lang file except "exclude"
        'exclude' => ['validation', 'passwords', 'pagination', 'auth'],
        'default_namespace' => 'common',
        'generate_types' => true,
    ],

    'inertia' => ['share' => true, 'key' => 'localization'],

    'scan_paths' => ['app', 'resources/views', 'resources/js'],
];
```

## Locale detection

Register the `locale` middleware alias, typically on the `web` group (`bootstrap/app.php`):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Scrapkit\LocalizationI18n\Http\Middleware\SetLocale::class,
    ]);
})
```

On every request the middleware resolves the locale with this priority:

1. `?locale=` query string or `{locale}` route parameter
2. session
3. `Accept-Language` header (quality-ordered, `it-IT` matches `it`)
4. `default_locale`

then calls `App::setLocale()`, applies the fallback locale and persists the result in the session (when one is available — stateless API requests work too).

Need a project-specific source, e.g. a user's saved preference? Implement `Scrapkit\LocalizationI18n\Contracts\LocaleResolver` and prepend it to `resolvers` — no package changes required.

### Switching locale

The package registers `PUT /locale` (named `locale.update`). It validates the locale against `supported_locales`, stores it in the session and redirects back. Disable it with `routes.switch_enabled` if you prefer your own endpoint.

## Backend translations

Nothing new to learn: use Laravel's native system (`lang/{locale}/*.php`, `__()`, `trans()`, validation files). The package only *reads* these files.

## Frontend workflow

```bash
php artisan translations:generate
```

converts `lang/{locale}/{namespace}.php` into:

```
resources/js/locales/
├── config.json          # { default, fallback, locales, defaultNamespace }
├── resources.d.ts       # typed t() keys, built from the default locale
├── it/common.json
├── it/users.json
├── en/common.json
└── en/users.json
```

Conversions applied:

| Laravel | i18next |
| --- | --- |
| `Ciao :name` | `Ciao {{name}}` |
| `:Name` / `:NAME` (cased variants) | `{{name}}` (case transform is lost — documented limitation) |
| `'apples' => 'mela\|mele'` | `"apples_one": "mela"`, `"apples_other": "mele"` |
| `{0} niente\|[1,*] qualcosa` (range syntax) | exported as-is with a warning — use simple two-form plurals in frontend namespaces |

Output is deterministic (recursively sorted keys, pretty-printed), so the generated files diff cleanly. Commit them and guard against drift in CI:

```bash
php artisan translations:generate --check   # exit 1 when out of date
```

### Wiring up React

After publishing the frontend stubs, initialise i18next once in `resources/js/app.tsx`:

```tsx
import './i18n';
```

Then use it anywhere:

```tsx
import { useTranslation } from 'react-i18next';
import { useLocale } from './i18n/use-locale';

function LanguageSwitcher() {
  const { t } = useTranslation();               // default namespace
  const { t: tUsers } = useTranslation('users'); // lazy-loads users.json
  const { locale, locales, setLocale } = useLocale();

  return (
    <select value={locale} onChange={(e) => void setLocale(e.target.value)}>
      {locales.map((code) => <option key={code}>{code}</option>)}
    </select>
  );
}
```

How it works:

- `backend.ts` maps `(language, namespace)` onto `import.meta.glob('../locales/*/*.json')`: Vite emits one hashed chunk per namespace, loaded on demand and cached by the browser. No runtime API calls, SSR-friendly.
- The initial language is read from `<html lang="...">`, which Laravel's Blade layout renders from `app()->getLocale()` — backend and frontend agree on the very first paint.
- `useLocale().setLocale()` updates i18next, `<html lang>`, the Laravel session (via `PUT /locale`) and reloads the current Inertia page.
- `resources.d.ts` augments i18next's types: a typo in `t('actions.sve')` is a compile error. Make sure `resolveJsonModule` is enabled and the `locales` directory is included in `tsconfig.json`.

### Inertia shared props

When Inertia is installed the current locale and the supported list are shared automatically:

```ts
const { localization } = usePage().props; // { locale: 'it', locales: ['it', 'en'] }
```

Disable with `inertia.share`.

## Keeping locales in sync

```bash
php artisan translations:check
```

Compares every supported locale against the default one and lists missing/extra keys in dot notation (`users.actions.create`). Exits non-zero on any difference — designed for CI.

```bash
php artisan translations:unused
```

Reports keys never referenced by `__()`, `trans()`, `trans_choice()`, `@lang()` or `t()` across `scan_paths`. Detection is static and heuristic: dynamically built keys (`t(\`users.${x}\`)`) cannot be seen, so the command **only reports** — it never deletes.

```bash
php artisan translations:add-locale fr
```

Scaffolds `lang/fr/` by copying the default locale's files (existing files are never overwritten). Translate them, add `'fr'` to `supported_locales`, run `translations:generate` — done. No code changes.

## Optional runtime API

If you need translations served over HTTP (e.g. `i18next-http-backend`, or editable translations without a rebuild), enable it:

```php
'api' => ['enabled' => true],
```

```
GET /api/translations/{locale}              → every frontend namespace
GET /api/translations/{locale}/{namespace}  → a single namespace
```

Responses carry an `ETag` and honour `If-None-Match` (304). Only the namespaces exposed to the frontend are served; excluded ones (e.g. `validation`) return 404.

## Suggested CI steps

```bash
php artisan translations:check              # locales in sync
php artisan translations:generate --check   # generated files not stale
```

## Testing

```bash
composer test      # Pest
composer analyse   # PHPStan
composer format    # Pint
```

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
