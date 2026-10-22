# Changelog

## 2.0.0 - 2026-10-22

The package API does not change. The breaking changes come from the new minimum versions of the dependencies, the `defaultSort` table prop, and the JS packaging. Read `UPGRADING.md` before you upgrade. Installs on `^1.x` are not affected.

### Changed (BREAKING)

- Require PHP `^8.3` (was `^8.1`).
- Require Laravel `^12.0|^13.0` (`illuminate/*`; was `^10.0`). Laravel 10 and 11 are no longer supported.
- Require `inertiajs/inertia-laravel` `^2.0.1|^3.0` (was `^0.6.9`).
- Require `spatie/laravel-data` `^4.13.1` (was `^3.7`). Your application's own Data classes must move to laravel-data 4.
- Require `spatie/laravel-query-builder` `^6.3.1|^7.0` (was `^5.6`). Your `query()` implementations use the query-builder API.
- Require `spatie/laravel-package-tools` `^1.19` (was `^1.14.0`).
- The `defaultSort` table prop and the `sort` fallback now send the default sort that you configure (`TableResource::$defaultSort` or `Table::defaultSort()`). In 1.x both were always `''`. Tables without a default sort still send `''`.
- JS: `@inertiajs/react` is now a peer dependency only, at `^2.0.0 || ^3.0.0` (was `^1.0.0`, and also listed in `dependencies`). Install it yourself. Inertia 3 requires React 19; the React peer range adds `^19`.
- JS: the package is ESM only. `main` and the `require` export are removed. The 1.2.1 `require()` entry already returned `{}`.

### Fixed

- `Filter::options()` works on laravel-data 4: it uses `FilterOption::collect($options, DataCollection::class)` instead of the removed `::collection()`.
- `make:table-resource` generates a class that runs on query-builder 6 and 7. The stub no longer emits `allowedFilters([])` or `allowedSorts([])`.
- The `make:table-resource` stub passes a subject to `builder()` (`builder(/* TODO: Model::class */)`). The old stub called `builder()` without arguments, which always failed.

### Removed

- The `InertiaTable` facade alias in `extra.laravel`. The facade class never existed.
- JS: the UMD/CommonJS build (`main`, `exports.require`).
- JS: the `engines` field (`node: ^16`).

### Internal

- Dev toolchain: Pest 4, PHPUnit 12, Testbench 10/11, Larastan 3 / PHPStan 2, Collision 8. `spatie/laravel-ray` removed.
- `phpunit.xml.dist` uses the PHPUnit 12.5 schema and no longer has a `<coverage>` block. `phpstan.neon.dist` drops `checkMissingIterableValueType`. PHPStan docblock fixes and a regenerated baseline.
- Feature tests for the table props, a named table, `Filter::options()` with integer values, the response macros, `make:table-resource` and `defaultSort`. `TestCase` registers the laravel-data, query-builder and Inertia service providers.
- CI: PHP 8.3–8.5 × Laravel 12/13 × prefer-lowest/prefer-stable, plus a query-builder 7 job. Because Composer 2.9+ blocks releases with security advisories, prefer-lowest effectively tests Laravel 12.69+ and 13.30+. PHPStan runs on PHP 8.3 and on pull requests.
- npm publishing checks that the release tag equals the `package.json` version and picks the dist-tag: pre-releases → `next`, a version higher than the current `latest` → `latest`, anything else → `v<major>-lts`.
- npm releases are staged through trusted publishing (OIDC) and published only after the owner runs `npm stage approve` with 2FA.
- The changelog workflow checks out `master`. Action versions bumped in all workflows.
- `.gitattributes` keeps the JS sources, JS config and the Yarn release out of the Composer dist.
- `composer.json` adds `branch-alias` `dev-master: 2.x-dev`. The `1.x` branch (from tag `1.2.1`) is the 1.x maintenance line, security fixes only.
- JS build: Node 22 (`.node-version`), Vite 7, vite-plugin-dts 4 with one rolled-up `index.d.ts`, TypeScript 5.9.
- JS: `@lifespikes/cogent-ts` moves from a peer dependency to a normal dependency. You no longer need to install it yourself.
