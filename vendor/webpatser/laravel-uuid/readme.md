# Laravel Uuid

[![Total Downloads](https://poser.pugx.org/webpatser/laravel-uuid/downloads.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![Build Status](https://secure.travis-ci.org/webpatser/laravel-uuid.png?branch=master)](http://travis-ci.org/webpatser/laravel-uuid)
[![Latest Stable Version](https://poser.pugx.org/webpatser/laravel-uuid/v/stable.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![Latest Unstable Version](https://poser.pugx.org/webpatser/laravel-uuid/v/unstable.svg)](https://packagist.org/packages/webpatser/laravel-uuid)

Laravel package to generate a UUID according to the RFC 4122 standard. Support for version 1, 3, 4 and 5 UUIDs are built-in.

Since Laravel `4.*` and `5.*` both rely on either `OpenSSL` or `Mcrypt`, the pseudo random byte generator now tries to use one of them. If both cannot be used (not a Laravel project?), the 'less random' `mt_rand()` function is used.

## What's new in 2.*
Laravel Uuid is now fully PSR-2, just like Laravel 5.1. Not that much has changed except for UPPERCASING the contants used in Laravel Uuid. Meaning `Uuid::nsDNS` is now `Uuid::NS_DNS` etc. Should be an easy fix.

For the 1.* branch check the docs [here](https://github.com/webpatser/laravel-uuid/tree/1.4)

## Installation

Add `webpatser/laravel-uuid` to `composer.json`.

```json
"webpatser/laravel-uuid": "2.*"
```
    
Run `composer update` to pull down the latest version of Laravel UUID.

Or install it directly from the command line using

```shell
composer require "webpatser/laravel-uuid:2.*"
```

For Laravel 4: edit `app/config/app.php` and add the `alias`

```php
'aliases' => array(
    // ommited
    'Uuid' => 'Webpatser\Uuid\Uuid',
)
```
    
For Laravel 5: edit `config/app.php` and add the `alias`

```php
'aliases' => [
    // ommited
    'Uuid' => Webpatser\Uuid\Uuid::class,
]
```

## Basic Usage

To quickly generate a UUID just do

```php
Uuid::generate()
```
	
This will generate a version 1 with a random ganerated MAC address.

## Advanced Usage

### UUID creation

Generate a version 1, time-based, UUID. You can set the optional node to the MAC address. If not supplied it will generate a random MAC address.

```php
Uuid::generate(1,'00:11:22:33:44:55');
```
	
Generate a version 3, name-based using MD5 hashing, UUID

```php
Uuid::generate(3,'test', Uuid::NS_DNS);
```	

Generate a version 4, truly random, UUID

```php
Uuid::generate(4);
```

Generate a version 5, name-based using SHA-1 hashing, UUID

```php
Uuid::generate(5,'test', Uuid::NS_DNS);
```
	
### Some magic features

To import a UUID

```php
$uuid = Uuid::import('d3d29d70-1d25-11e3-8591-034165a3a613');
```	

Extract the time for a time-based UUID (version 1)

```php
$uuid = Uuid::generate(1);
dd($uuid->time);
```

Extract the version of an UUID

```php
$uuid = Uuid::generate(4);
dd($uuid->version);
````

## Notes

Full details on the UUID specification can be found [here](http://tools.ietf.org/html/rfc4122)
