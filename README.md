# middlewares/emitter

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]

Middleware to send (or emit) a PSR-7 response object using `header()` and `echo` and return the sent response. This middleware is intended to go at the top of the middleware stack in order to get the response generated by the inner middlewares and send to the browser.

## Requirements

* PHP >= 7.2
* A [PSR-7 http library](https://github.com/middlewares/awesome-psr15-middlewares#psr-7-implementations)
* A [PSR-15 middleware dispatcher](https://github.com/middlewares/awesome-psr15-middlewares#dispatcher)

## Installation

This package is installable and autoloadable via Composer as [middlewares/emitter](https://packagist.org/packages/middlewares/emitter).

```sh
composer require middlewares/emitter
```

## Example

```php
$dispatcher = new Dispatcher([
    new Middlewares\Emitter(),
    // Here the rest of your middlewares
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

## Options

#### `maxBufferLength(int $maxBufferLength)`

Maximum output buffering size for each iteration. By default is 8192 bytes.

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/emitter.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/middlewares/emitter/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/g/middlewares/emitter.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/emitter.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/emitter
[link-travis]: https://travis-ci.org/middlewares/emitter
[link-scrutinizer]: https://scrutinizer-ci.com/g/middlewares/emitter
[link-downloads]: https://packagist.org/packages/middlewares/emitter
