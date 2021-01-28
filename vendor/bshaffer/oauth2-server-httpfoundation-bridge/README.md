oauth2-server-httpfoundation-bridge
===================================

A bridge to [HttpFoundation](https://github.com/symfony/httpfoundation) for [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php).

[![Build Status](https://secure.travis-ci.org/bshaffer/oauth2-server-httpfoundation-bridge.png)](http://travis-ci.org/bshaffer/oauth2-server-httpfoundation-bridge)

`oauth2-server-httpfoundation-bridge` is a wrapper for [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php)
which returns `Symfony\Component\HttpFoundation\Response` instead of `OAuth2\Response`, and uses `Symfony\Component\HttpFoundation\Request` instead of `OAuth2\Request`.

If you are integrating OAuth2 into a Silex, Symfony, or [Laravel 4](http://four.laravel.com) app, (or any app using `HttpFoundation`), this will make your application much cleaner

Installation
------------

[Composer](http://getcomposer.org/) is the best way to install this library.  Add this line to composer.json:

```
{
    "require": {
        "bshaffer/oauth2-server-httpfoundation-bridge": "v1.0",
        ...
    },
    ...
}
```

And then run `composer.phar install`

> Match tags with the [oauth2-server-php](https://github.com/bshaffer/oauth2-server-php) library when possible.
> [`v1.1`](https://github.com/bshaffer/oauth2-server-httpfoundation-bridge/tree/v1.1) is the latest tag.

## Creating the request

Creating the request object is the same as before, except now you use the
`OAuth2\HttpFoundationBridge\Request` class:

```php
$request = OAuth2\HttpFoundationBridge\Request::createFromGlobals();
$app->run($request);
```

The Request object is now compatible with both HttpFoundation *and* oauth2-server-php

```php
// getBaseUrl is unique to HttpFoundation
$baseUrl = $request->getBaseUrl();

// call oauth server
$server->grantAccessToken($request);
```

If the HttpFoundation request already exists, you can use the static `createFromRequest`
function to build the OAuth2\HttpFoundationBridge\Request instance:

```php
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;

// in your controller layer, the $request object is passed in
public function execute(Request $request)
{
    //... (instantiate server/response objects)
    $bridgeRequest = BridgeRequest::createFromRequest($request);
    $server->grantAccessToken($bridgeRequest, $response);
}
```

## Creating the response

The `OAuth2\HttpFoundationBridge\Response` object extends `Symfony\Component\HttpFoundation\JsonResponse`,
and implements `OAuth2\ResponseInterface`, allowing you to pass this in and return it from your controllers.
In Symfony and Silex, this will be all that is needed to integrate the server:

```php
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;

// in your controller layer, the $request object is passed in
public function execute(Request $request)
{
    //... (instantiate server/response objects)
    $response = new BridgeResponse();
    return $server->handleTokenRequest($request, $response);
}
```

> Note: this object will return JSON.  Implement your own class using `OAuth2\ResponseInterface` to support
> a different content-type.

## Examples

 * OAuth2 Request - [Silex Integration](https://github.com/bshaffer/oauth2-demo-php/blob/master/web/index.php#L47)
 * OAuth2 Response - [Silex Integration](https://github.com/bshaffer/oauth2-demo-php/blob/master/src/OAuth2Demo/Server/Controllers/Token.php#L26)

Contact
-------

Please contact Brent Shaffer (bshafs <at> gmail <dot> com) for more information
