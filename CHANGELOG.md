# Changelog

All Notable changes to `extractor` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## Unreleased

### Added
- A test suite of 62 tests: unit tests for the request, the response, the bag, the middleware chain, the cache
  middleware, the traits, the micro client and the generators, and feature tests that send real requests over a real
  connection to a stub server the suite starts (`tests/Support/StubServer.php`). It covers 98% of `src/`.
- GitHub Actions workflows running the test suite (PHP 8.4 and 8.5, Laravel 12 and 13, lowest and highest
  dependencies), the coding style check, the static analysis and the code coverage on every pull request and on every
  push to `master`. The coverage has to stay above 95%.
- A `Dockerfile` (with pcov, for coverage) and a `Makefile` to run the test suite and every check inside a container,
  so that no PHP installation is needed on the host.
- A `phpcs.xml.dist` ruleset, a `phpstan.neon.dist` configuration (level 7 with larastan) and a `rector.php`
  configuration, plus the `test-coverage`, `analyse`, `rector` and `ci` composer scripts.
- `Request::withoutGlobalMiddlewares()`, to throw the global middlewares away again — a test suite needs it, and so
  does an application that registers them conditionally.
- `Request::getMiddlewares()` and `Request::getAllowRedirects()`, so that what a request would do can be read before
  it is sent.
- `Request::createClient()` and `Bag::createClient()` are `protected` seams: a test — or an application that has to
  hand its own handler stack over — can replace the guzzle client by extending the class.

### Changed
- **Breaking:** PHP 8.4 is now the minimum required version (was PHP 7.2), and only the last two major versions of
  Laravel are supported: `^12.0|^13.0` (was `5.8` and up). `illuminate/broadcasting` was never used and is gone,
  `illuminate/console` — the home of the generators of the package — is declared now.
- **Breaking:** `guzzlehttp/guzzle` is required as `^7.8.2|^8.0` (was `6.2.*|7.*`).
- The package was modernized for PHP 8.4. Every parameter, return value and property of `src/` declares a type now,
  promoted and `readonly` constructor properties are used, nullable types are spelled `T|null`, and the two-space
  indentation of `Traits/Conditional.php` is gone.
- **Breaking:** `RequestInterface` declares the return types of its methods (`static` for the ones that chain), and
  it declares `getOptions()`, `onSuccess()`, `onError()`, `success()` and `error()` — the methods the bag and the
  middleware chain of this package have always called on a request.
- **Breaking:** `RequestInterface::getHeader()` and `Request::getHeader()` declare `string|null`. A header that was
  never added used to end in a `TypeError`, since `null` was returned from a method that declares `string`.
- **Breaking:** `Request::getMultipartData()`, `getFormParam()`, `getQuery()` and the setters declare their types.
  `Request::addMultipartData()` replaces `addMultiparData()`, which stays as a deprecated alias.
- The middlewares that are banned with `withoutMiddleware()` are told apart with a comparison instead of
  `array_diff()`, which turned every middleware into a string on the way and needed a `__toString()` for that.
- A request that a middleware answers with `null` for now raises a `RuntimeException` that says so, instead of a
  "call to a member function getStatusCode() on null".
- `allow_redirects` is part of the options a request is sent with. `allowRedirects(false)` used to be remembered and
  then never handed to the client.

### Fixed
- **`HttpURL::parseQueryString()`** checked the empty array it had just created instead of the query string it was
  given, so it always answered with an empty list. Together with a `getParsedQueryString()` that was called with an
  argument it does not take, the query string of a uri was dropped: `new Request('https://example.com/?page=2')` sent
  no `page` at all.
- **`Bag`:** the `rejected` handler of the pool read two keys that do not exist off the request it was handling, which
  overwrote the callback the caller gave with `null` — a request that never reached its server told nobody. The
  response of a failed request carries the reason now, and `$requests` starts out as an array, so an empty bag no
  longer ends in a `TypeError`.
- **`CacheMiddleware`:** the key of a cache entry was built by serializing the whole request, which throws as soon as
  the request carries an event callback ("Serialization of 'Closure' is not allowed"). It is built from the method,
  the uri and the options of the request now.
- **The generators:** the stub of a class was looked up under `base_path('vendor/shetabit/extractor/…')`, which is
  only there when the package sits in that very directory — a path repository, a custom vendor directory or the test
  suite of the package itself all ended in a "file does not exist". The stubs are looked up next to the commands now,
  and they use the `{{ class }}` placeholders of the current Laravel generators.

### Removed
- **Breaking:** `Shetabit\Extractor\Middlewares\Cache`, a copy of `CacheMiddleware` that could not work: it called
  `Cache::has()`, `Cache::get()` and `Cache::remember()` without importing the facade, so every call landed on the
  middleware itself. `CacheMiddleware` is the one the `cache()` shortcut has always used.

## Date - 2019-01-09

### Fixed
- Nothing

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing
