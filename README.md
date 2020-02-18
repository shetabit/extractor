<p align="center">
    <img src="resources/images/microservices-communication.png?raw=true">
</p>

# Laravel Extractor

a `micro-client` generator to communicate between `microservices` in Laravel applications.

## List of contents

- [Install](#install)
- [How to use](#how-to-use)
  - [Send Request to remote API](#sent-request-to-remote-api)
    - [Middlewares](#middlewares)
    - [Cache](#cache)
    - [Conditional configs](#conditional-configs)
    - [Request available methods](#request-available-methods)
  - [Micro-clients](#micro-clients)
    - [Create micro-clients](#create-micro-clients)
    - [Run a micro-client](#run-a-micro-client)
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
],
```

## How to use

#### Sent request to remote API

you can send requests to remote API using `Request` class, see the below example:

```php
// at the top
use Shetabit\Extractor\Classes\Request;

//...

// create new request
$request = new Request();

// set api's url and method
$request->setUri('http://yoursite.com/api/v1/endpoint')
		->setMethod('get');

// run the request and get data
$response = $request->fetch();

var_dump($response); // show given response

```

as you see, you can work with remote API in an easy way.

the `Request` has more methods to add `fileds`, `headers` and etc.

##### Middlewares

<p align="center">
    <img src="resources/images/middlewares-chain.png?raw=true">
</p>

you can manipulate and substitute Requests and responses using middlewares.

middlewares can be created by running the below command

```shell
php artisan make:micro-client-middleware test
```

the former command will create a middleware named `test` in `app\Http\MicroClients\Middlewares` path.

middlewares can be used like the below:

```php
$request
    ->setUri('http://your-site.com')
    ->setMethod('get')
    ->middleware(new AuthMiddleware)
    ->fetch();
```

multiple middlewares can be used by calling `middleware` method multiple times:

```php
$request
    ->setUri('http://your-site.com')
    ->setMethod('get')
    ->middleware(new Test1)
    ->middleware(new Test2)
    ->fetch();
```

each middleware has a `handle` method that can be used to handle requests and responses.

the following middleware would perform some task before the request is handled by the application:

```php
public function handle($request, Closure $next) {
    if($user->name == 'john') {
        $request->addQuery('name', 'john');
    }

    return $next($request);
}
```

However, this middleware would perform its task after the request is handled by the application:

```php
public function handle($request, Closure $next)
{
    $response = $next($request);

    // Perform action

    return $response;
}
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

##### Conditional configs

sometimes you need to add some configs when a condition happens.
you can use `when` method to add conditional configs

```php
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

##### Request available methods:

- `setUri(string $uri)` : set API end point.
- `getUri()` : retrieve current end point.
- `setMethod(string $method)` : set method (get, post, patch, put, delete).
- `getMethod()` : get current method.
- `addHeader(string $name, string $value)` : set a header.
- `getHeader(string $name)` : get a header by its name.
- `getHeaders()` : retrieve all headers.
- `setTimeout(int $timeout)` : set request timeout (seconds).
- `getTimeout()` : retrieve timeout (seconds).
- `setProxy(string|array $proxy)` : proxy the request.
- `getProxy()` : retrieve proxy.
- `setBody(string $body)` : set request body.
- `getVerify()` : retrieve the SSL certificate verification behavior of a request.
- `setVerify(boolean|string $verify)` : set SSL certificate verification.
- `getBody()`: retrieve request body.
- `addFormParam(string $name, string $value)` : add parameters into request similar to html forms.
- `getFormParam(string $name)` : get a form parameter value by its name.
- `getFormParams()` : retrieve all current form parameters.
- `AddMultipartData(string $name, string $value, array $headers)` : add multipart data (multipart/form-data), you can send files using this method.
- `getMultipartData(string $name)` : get current multipart data using its name.
- `addQuery(string $name, string $value)` : add query string into current request.
- `getQuery($name)` : get a query by its name.
- `getQueries()` : get all queries.
- `fetch(callable $resolve, callable $reject)` : runs the request, if fails , the `reject` will be called, if succeed then resolve will be called.
- `send(callable $resolve, callable $reject)` : alias of `fetch`.
- `createBag(callable $resolve, callable $reject)` : creates  a bag (group) of concurrent requests.
- `cache` : store responses in cache
- `middleware` : add middlewares

## Micro clients

This package handles **communications** between **micro-services** using **micro-clients**

#### Create micro-clients

micro clients can be generated using commands.

```bash
php artisan make:micro-client  clientName
```

micro-clients will saved in `app/Http/MicroClients` by default.

lets create and example, imagine you have and remote Api (or microservice) and need to login into it.

then, your Login micro-client can be similar to below codes:

```php
namespace App\Http\MicroClients\Auth;

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

#### Run a micro-client

you can run the `Login` micro-client like the below (we have Login micro-client example at the top)

```php
// dump data
$username = 'test';
$password = 'something';

$client = new Login($username, $password);

// run client and login into remote service (remote api)
$response = $client->run();
```

micro-client starts to work as you call `run` method.

#### Send requests

use the `run` method to handle micro-client

```php
$this->request->setUri('remote-url.com')->fetch();
```

in each micro-client, you have access to `request` object, it can be used to handle communications between micro-services.

#### Send concurrent requests

```php
use Shetabit\Extractor\Classes\Request;
use Shetabit\Extractor\Contracts\RequestInterface;

// ...

$result = new Request;

$responses = $result
    ->createBag()
    ->addRequest(function(RequestInterface $request) {
        $request->setUri('http://google.com/');
    })
    ->addRequest(function(RequestInterface $request) {
        $request->setUri('http://bing.com/');
    })
    ->fetch();
```

you can set `success` and `error` listener for each requests seperately. here is another example that uses `onSuccess` and `onError` listeners.

```php
$response = $result
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

## On progress features

- internal error exceptions

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
