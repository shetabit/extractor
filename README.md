
<p align="center">
    <img src="resources/images/microservices-communication.png?raw=true">
</p>



# Laravel Extractor

a `micro-client` generator to communicate between `microservices` in Laravel applications.

## List of contents

- [Install](#install)
- [How to use](#how-to-use)
  - [Micro-clients](#micro-clients)
    - [Create micro-clients](#create-micro-clients)
    - [Run a micro-client](#run-a-micro-client)
    - [Send requests](#send-requests)
- [Change log](#change-log)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## install

Via Composer

```bash
$ composer require shetabit/extractor
```

If you are using `Laravel 5.5` or higher then you don't need to add the provider and alias.

In your `config/app.php` file add below lines.

```php
# In your providers array.
'providers' => [
	...
	Shetabit\Extractor\Providers\ExtractorServiceProvider::class,
],
```

## How to use

This package handles **communications** between **micro-services** using **micro-clients**

#### Create micro-clients

micro clients can be generated using commands.

```bash
php artisan make:micro-client  clientName
```

micro-clients will saved in `app/Http/MicroClients` by default.

#### Run a micro-client

you can run the micro-client like the below

```php
$client = new UploadFileClient();

$client->run();
```

micro-client starts to work as you call `run` method.

#### Send requests

use the `run` method to handle micro-client

```php
$this->request->setUri('remote-url.com')->fetch();
```

in each micro-client, you have access to `request` object, it can be used to handle communications between micro-services.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email khanzadimahdi@gmail.com instead of using the issue tracker.

## Credits

- [Mahdi khanzadi][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-packagist]: https://packagist.org/packages/shetabit/extractor
[link-code-quality]: https://scrutinizer-ci.com/g/shetabit/extractor
[link-author]: https://github.com/khanzadimahdi
[link-contributors]: ../../contributors
