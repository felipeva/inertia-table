# Upgrading

## From 1.x to 2.0

This guide covers both packages:

- `felipeva/inertia-table` (Composer)
- `@felipeva/inertiajs-table-react` (npm)

Both packages are released together with the same version. Upgrade them together.

### Requirements

| Dependency | 1.x | 2.0 |
|---|---|---|
| PHP | `^8.1` | `^8.3` |
| Laravel (`illuminate/*`) | `^10.0` | `^12.0 \| ^13.0` |
| `inertiajs/inertia-laravel` | `^0.6.9` | `^2.0.1 \| ^3.0` |
| `spatie/laravel-data` | `^3.7` | `^4.13.1` |
| `spatie/laravel-query-builder` | `^5.6` | `^6.3.1 \| ^7.0` |
| `spatie/laravel-package-tools` | `^1.14.0` | `^1.19` |
| `@inertiajs/react` | `^1.0.0` (bundled as a dependency) | `^2.0.0 \|\| ^3.0.0` (peer dependency only) |
| `react` | `^16.9 \|\| ^17 \|\| ^18` | `^16.9 \|\| ^17 \|\| ^18 \|\| ^19` |

Inertia 3 requires React 19.

The CI tests Laravel 12.69+ and 13.30+ only. Composer 2.9+ blocks older releases by default because of the security advisory PKSA-d5tc-s1qs-h781.

### Why this is a major release

The public API of the package does not change. `TableResource`, `Table`, the `builder()` signature, the response macros and the exported JS hooks and types are the same as in 1.2.1.

The breaking changes come from:

- The dependencies. Your application can install only one major version of Laravel, laravel-data, query-builder and Inertia. When the package moves to a new major version, your application must move too.
- The `defaultSort` and `sort` table props. They now send the default sort that you configure (see [Behaviour changes](#behaviour-changes)).
- The JS packaging. `@inertiajs/react` is a peer dependency only, and the package is ESM only.

If your application stays on `^1.x`, nothing changes. Composer and npm do not resolve 2.x from a `^1.x` constraint.

### Upgrade order

1. Migrate your application code first. Use the upstream guides:
   - Laravel: [10 to 11](https://laravel.com/framework/docs/11.x/upgrade), [11 to 12](https://laravel.com/framework/docs/12.x/upgrade), [12 to 13](https://laravel.com/framework/docs/13.x/upgrade)
   - [laravel-data](https://github.com/spatie/laravel-data/blob/main/UPGRADING.md) (v3 to v4)
   - [laravel-query-builder](https://github.com/spatie/laravel-query-builder/blob/main/UPGRADING.md) (v5 to v6, and v6 to v7 if you want v7)
   - Inertia: [v2](https://inertiajs.com/docs/v2/getting-started/upgrade-guide), [v3](https://inertiajs.com/docs/v3/getting-started/upgrade-guide)
2. Update all the Composer constraints in one step. Version 1.x of this package requires Laravel 10, laravel-data 3, query-builder 5 and inertia-laravel 0.6. Composer cannot upgrade any of them while 1.x is installed. For example:

   ```bash
   composer require felipeva/inertia-table:^2.0 laravel/framework:^12.0 \
       inertiajs/inertia-laravel:^2.0 spatie/laravel-data:^4.13.1 \
       spatie/laravel-query-builder:^6.3.1 --with-all-dependencies
   ```

3. Update the JS packages:

   ```bash
   npm install @felipeva/inertiajs-table-react@^2 @inertiajs/react@^2
   ```

### laravel-data 4

laravel-data 4 removed `Data::collection()`. Use `Data::collect()`:

```php
// laravel-data 3
FilterOption::collection($options);

// laravel-data 4
FilterOption::collect($options, DataCollection::class);
```

The package already makes this change in `Filter::options()`. You must change your own code only if you call `::collection()` on the package's Data classes (`Column`, `Filter`, `FilterOption`, `Search`) or on your subclasses of them.

### query-builder 6 and 7

- **v6:** custom filter, sort and include classes must declare property and parameter types.
- **v7:** `allowedFilters()`, `allowedSorts()`, `allowedIncludes()`, `allowedFields()`, `defaultSort()` and `defaultSorts()` take variadic arguments. An array argument causes a `TypeError`.

  ```php
  // v6
  $this->builder(User::class)->allowedFilters(['name', 'email']);

  // v7
  $this->builder(User::class)->allowedFilters('name', 'email');
  ```

- **v7:** the `'*'` wildcard is removed. The `count_suffix` and `exists_suffix` config keys moved to a `suffixes` array.
- **v7.3.6 and later** require PHP 8.4. On PHP 8.3, Composer installs 7.3.5 or earlier.

You can stay on query-builder 6. The package supports both versions.

### Inertia 2 and 3

- Upgrade the server adapter (`inertiajs/inertia-laravel`) and the client adapter (`@inertiajs/react`) to the same major version. The Inertia docs do not say that mixed major versions work.
- Inertia 3 removes `Inertia::lazy()` and the `LazyProp` class. Use `Inertia::optional()`.
- Inertia 3 requires React 19.

### JS package

#### `@inertiajs/react` is a peer dependency only

In 1.x the package listed `@inertiajs/react` in `dependencies` and in `peerDependencies`. This could install a second copy of Inertia 1.x inside the package. With two copies, `usePage()` in the package hooks could throw.

In 2.0, install `@inertiajs/react` yourself, at `^2.0.0` or `^3.0.0`. The package uses your copy.

`@lifespikes/cogent-ts` is now a normal dependency. You do not have to install it yourself.

#### ESM only

The package ships only an ES module build. `main` and the `require` export are removed. `require('@felipeva/inertiajs-table-react')` is not supported. In 1.2.1 the `require()` entry already returned an empty object, so no working entry is lost.

The type declarations are now one file, `dist/index.d.ts`. The exported names are the same as in 1.2.1.

The `engines` field (`node: ^16`) is removed.

#### Types removed in 1.2.0

Version 1.2.0 removed these types (commit `334f094`). If you upgrade from 1.0.x or 1.1.x and import one of them, define it in your own code:

- `ActiveFiltersProps`
- `ChildrenProps`
- `ColumnMenuProps`
- `FilterItemProps`
- `FilterMenuProps`
- `NewSavedFilterModalProps`
- `PaginationPosition`
- `PerPageOptionSelectorProps`
- `RenderAction`
- `RenderFooter`
- `RenderHeader`
- `RenderRow`
- `RenderSelectedRowActions`
- `RowWithActions`
- `SavedFilterMenuProps`
- `SearchInputProps`
- `SearchMenuProps`
- `SelectedRecordRowProps`
- `TableFooterProps`
- `TableItemType`
- `UseSavedFiltersOptions`

For example, `PaginationPosition` was:

```ts
export type PaginationPosition = 'top' | 'bottom' | 'both';
```

Versions 1.0.0 to 1.0.4 also exported `LeftRightPaginationProps`, `PageInactiveProps`, `PageLinkProps`, `PaginationProps`, `Row`, `RowWrapper`, `TableColumnItemProps`, `TableHeaderProps`, `TableProps` and `TableRowItemProps`. Version 1.0.6 removed them.

### Generated table resources

The `make:table-resource` stub changed:

```php
// 1.x
return $this->builder()
    ->defaultSort('-created_at')
    ->allowedFilters([])
    ->allowedSorts([]);

// 2.0
return $this->builder(/* TODO: Model::class */)
    ->defaultSort('-created_at')
    ->allowedSorts('created_at');
```

The 1.x stub did not run on any query-builder version: `builder()` requires a subject. On query-builder 7, `allowedFilters([])` and `allowedSorts([])` also cause a `TypeError`. Check the classes that you generated with 1.x. Give `builder()` a model, and remove the empty `allowed*([])` calls.

### Behaviour changes

#### `defaultSort` and `sort` table props

In 1.x the `defaultSort` table prop was always `''`. The `sort` prop also fell back to `''` when the request had no sort parameter. The default sort that you set did not reach the frontend.

In 2.0 both props use the default sort that you set with the `$defaultSort` property of a `TableResource` or with `Table::defaultSort()`:

```php
class UserTable extends TableResource
{
    protected string $defaultSort = '-created_at';
}

// Request without a sort parameter:
// 1.x: 'defaultSort' => '', 'sort' => ''
// 2.0: 'defaultSort' => '-created_at', 'sort' => '-created_at'
```

Tables without a default sort still send `''`. The sort state of each column already used the default sort in 1.x, and it does not change.

If your frontend reads `defaultSort` or `sort` and expects `''`, update it.

`->defaultSort()` on the query builder inside `query()` does not set these props.

#### `links[].page` in the paginator JSON

Laravel 12.69 and later add a `page` key to each item of the paginator `links` array. This comes from Laravel, not from this package. The `Link` type of the JS package does not declare `page`. If you read it, extend the type in your own code.

#### `TableResource::make()` and constructor defaults

`TableResource::make()` resolves the resource from the container (`app(static::class, $parameters)`). Since Laravel 12, the container uses the default value of a nullable class parameter instead of resolving an instance:

```php
class UserTable extends TableResource
{
    public function __construct(public ?Team $team = null)
    {
        parent::__construct();
    }
}

UserTable::make()->team; // Laravel 10: a Team instance. Laravel 12+: null.
```

Pass the parameter to `make()` explicitly, or remove the default value. See [Container class dependency resolution](https://laravel.com/framework/docs/12.x/upgrade#container-class-dependency-resolution).

### Support policy for 1.x

- The `1.x` branch is the maintenance line for 1.x. It starts at tag `1.2.1`.
- 1.x gets security fixes only.
- npm publishes 1.x releases under the `v1-lts` dist-tag. `latest` stays on 2.x.
- A `^1.x` constraint in Composer or npm keeps resolving the newest 1.x release.
