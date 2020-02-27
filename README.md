<p align="center">
    <img src="resources/images/microservices-communication.png?raw=true">
</p>

# Laravel Extractor

Communicate with **remote servers** or **microservices** in an easy way.

All requests and responses can be **cached** and **manipulated** on runtime using **middlewares**.

[Donate me](https://yekpay.me/mahdikhanzadi) if you like this package :sunglasses: :bowtie:

## List of contents

- [Install](#install)
- [How to use](#how-to-use)
  - [Send requests](#send-requests)
  - [Send concurrent requests](#send-concurrent-requests)
  - [Event listeners](#event-listeners)
  - [Middlewares](#middlewares)
  	- [How to create](#how-to-create)
  	- [Global middlewares](#global-middlewares)
  - [Cache](#cache)
  - [Conditional configs](#conditional-configs)
  - [Clients](#Clients)
    - [Create clients](#create-clients)
    - [Run a client](#run-a-client)
    - [Send requests](#send-requests)
    - [Send concurrent requests](#send-concurrent-requests)
- [Change log](#change-log)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Install

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
]
```

## How to use

#### Send requests

you can send requests to remote API using `Request` class, see the below example:

```php
// at the top
use Shetabit\Extractor\Classes\Request;

//...

// create new request
$request = new Request();

// set api's url and method
$request->setUri($url)->setMethod('get');

// run the request and get data
$response = $request->fetch();

var_dump($response); // show given response
```

as you see, you can work with remote API in an easy way.

the `Request` has more methods to add `fields`, `headers` and etc.

```php
use Shetabit\Extractor\Classes\Request;

//...
$request = new Request();


# Example 1:
$request
	->setUri('http://your-site.com')
	->setMethod('post')
	// add some headers
	->addHeader('Authorization', "Bearer dfaerfaeaeva1351adsfaecva")
	->addHeader('Accept', 'application/json')
	// add form parameters
	->addFormParam('email', $email)
    ->addFormParam('password', $password);

$response = $request->fetch(); // run request


# Example 2:
$request
	->setUri('http://your-site.com')
	->setMethod('get')
	// add query string
	->addQuery('page', $page)
	->addQuery('s', $search);

$response = $request->fetch(); // run request
```

#### Send concurrent requests

you can send concurrent requests like the below

```php
use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\RequestInterface;

// ...

$request = new Request;

$responses = $request
    ->createBag()
    ->addRequest(function(RequestInterface $request) {
        $request->setUri('http://google.com/');
    })
    ->addRequest(function(RequestInterface $request) {
        $request->setUri('http://bing.com/');
    })
    ->fetch();
```

#### Event listeners

you can set `success` and `error` listener for each requests seperately. here is another example that uses `onSuccess` and `onError` listeners.

```php
use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\RequestInterface;

// ...

$request = new Request;

# Example 1: using on success
$response = $request
	->setUri('http://google.com/')
	->onSuccess(function (ResponseInterface $response, RequestInterface $request) {
		echo $response->getBody();
	})
	->fetch();


# Example 2: using on error
$response = $request
	->setUri('http://yahoo.com/')
    ->onSuccess(function (ResponseInterface $response, RequestInterface $request) {
                echo 'success';
            })
            ->onError(function (ResponseInterface $response, RequestInterface $request) {
                echo 'fail';
            });


# Example 3: using request's bag
$response = $request
    ->createBag()
    ->addRequest(function (RequestInterface $request) {
        $request
            ->setUri('http://google.com/')
            ->onSuccess(function (ResponseInterface $response, RequestInterface $request) {
                echo $response->getBody();
            });
    })
    ->addRequest(function (RequestInterface $request) {
        $request
            ->setUri('http://yahoo.com/')
            ->onSuccess(function (ResponseInterface $response, RequestInterface $request) {
                echo 'success';
            })
            ->onError(function (ResponseInterface $response, RequestInterface $request) {
                echo 'fail';
            });
    })
    ->fetch();
```

#### Middlewares

<p align="center">
    <img src="resources/images/middlewares-chain.png?raw=true">
</p>



##### How to create

Middlewares can be created by running the below command

```shell
php artisan make:extractor-middleware test
```

The former command will create a middleware named `test` in `app\Http\RemoteRequests\Middlewares` path.

You can add a middleware to request like the below:

```php
$request
    ->setUri('http://your-site.com')
    ->setMethod('get')
    ->middleware(new AuthMiddleware)
    ->fetch();
```

Multiple middlewares can be used by calling `middleware` method multiple times:

```php
$request
    ->setUri('http://your-site.com')
    ->setMethod('get')
    ->middleware(new Test1)
    ->middleware(new Test2)
    ->fetch();
```

Each middleware has a `handle` method that can be used to handle requests and responses.

The following middleware would perform some task before the request is handled by the application:

```php
public function handle($request, Closure $next) {
    if($user->name == 'john') {
        $request->addQuery('name', 'john');
    }

    return $next($request);
}
```

However,  this middleware would perform its task after the request is handled by the application:

```php
public function handle($request, Closure $next)
{
    $response = $next($request);

    // Perform action

    return $response;
}
```

##### Global middlewares

You can use `Request::withGlobalMiddlewares` to add global middlewares.
global middlewares will be binded to all requests.

```php

// in your AppServiceProvider

protected boot()
{
    Request::withGlobalMiddlewares([
        // list of middlewares
    ]);
}

```

in each request, you can unbind global middlewares, if you need them just use `withoutMiddleware` like the below:

```php
// at the top
use Shetabit\Extractor\Classes\Request;

$url = 'http://google.com/';

$response = (new Request)
	->setUri($url)
	->withoutMiddleware(new TestMiddleware)
	->fetch();
```


##### Cache

you can cache responses according to requests.

```php
// at the top
use Shetabit\Extractor\Classes\Request;

$url = 'http://google.com/';
$ttl = 5; // 5 seconds

$response = (new Request)->setUri($url)->cache($ttl)->fetch();
```

**Notice:** `TTL` (Time To Live) is the same as `Laravel` cache.

```php
// at the top
use Shetabit\Extractor\Classes\Request;

$url = 'http://google.com/';
$ttl = now()->addMinutes(10); // 10 minutes

$response = (new Request)->setUri($url)->cache($ttl)->fetch();
```

#### Conditional configs

Sometimes you need to add some configs when a condition happens, in this kind of situations you can use the `when` method to add conditional configs.

```php
# Example 1: simple

$request
    ->when('condition1', function($request) {
        $request
            ->setUri('http://your-site.com')
            ->setMethod('get')
            ->middleware(new AuthMiddleware);
    });


// Example 2: nested
$request
    ->when('condition1', function($request) {
        $request
            ->setUri('http://your-site.com')
            ->setMethod('get')
            ->middleware(new AuthMiddleware);
    })
    ->when('condition2', function($request) {
        $request
            ->setUri('http://shop-site.com')
            ->setMethod('get');
    })
    ->whenNot('condition3', function($request) {
        $request
            ->setUri('http://shop-site.com')
            ->setMethod('patch')
            ->when('condition4', function($request) {
                $request->setMethod('delete'); // sets method to delete
            });
    })
    ->fetch();
```

#### Client

You can encapsulate any request that exists between the **current microservice** and the **remote microservice** within a `Client`.

#### Create clients

Clients can be created using a simple command

```bash
php artisan make:extractor-client  clientName
```

Clients will saved in `app/Http/RemoteRequests/Clients` by default.

lets create and example, imagine you have and remote Api (or microservice) and need to login into it.

then, your Login micro-client can be similar to below codes:

```php
namespace App\Http\RemoteRequests\Clients\Auth;

use Shetabit\Extractor\Abstracts\MicroClientAbstract;
use Shetabit\Extractor\Contracts\ResponseInterface;

class Login extends MicroClientAbstract
{
    protected $mobile;
    protected $password;

    public function __construct($username, $password = null)
    {
        $this->username = $username;
        $this->password = $password;

        parent::__construct();
    }

    /**
     * Get requests' endpoint
     *
     * @return string
     */
    protected function getEndPoint()
    {
        return 'http://yoursite.com/api/v1/auth';
    }

    /**
     * Run client
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function run() : ResponseInterface
    {
        $response = $this
            ->request
            ->setUri($this->getEndPoint())
            ->setMethod('post')
            ->addFormParam('username', $this->username)
            ->addFormParam('password', $this->password)
            ->fetch();

         return $response;
    }
}
```

#### Run a client

you can run the `Login` micro-client like the below (we have Login client example at the top)

```php
// dump data
$username = 'test';
$password = 'something';

$client = new Login($username, $password);

// run client and login into remote service (remote api)
$response = $client->run();

// dump show response's body
var_dump($response->getBody());
```

as you see, client starts to work as you call the `run` method, fetches and returns a response.

## On progress features

- internal error exceptions
- resource and API resource clients
- proxy requests to another server (middleware)

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
