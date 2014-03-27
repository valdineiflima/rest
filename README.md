# REST Client for Laravel 4

A simple [Laravel 4](http://four.laravel.com/) client rest

## Installation

The Rest Service Provider can be installed via [Composer](http://getcomposer.org) by requiring the
`rdehnhardt/rest` package in your project's `composer.json`.

```json
{
    "require": {
        "rdehnhardt/rest": "1.*"
    }
}
```

Then run a composer update
```sh
php composer.phar update
```

## Configuration

To use the Rest Service Provider, you must register the provider when bootstrapping your Laravel application.

Publish the package configuration using Artisan.

```sh
php artisan config:publish rdehnhardt/rest
```

Update your settings in the generated `app/config/packages/aws/aws-sdk-php-laravel` configuration file.

```php
return array(
    'server'    => 'YOUR_API_SERVER',
	'http' => array(
		'user' => 'YOUR_USER_API',
		'password' => 'YOUR_PASS_API',
		'auth' => false
	)
);
```

Find the `providers` key in your `app/config/app.php` and register the AWS Service Provider.

```php
    'providers' => array(
        // ...
        'Rdehnhardt\Rest\RestServiceProvider',
    )
```

The rest client needs not be entered in the alias.

## Usage

In order to use the Rest for PHP within your app, you need to retrieve it from the [Laravel IoC
Container](http://four.laravel.com/docs/ioc).

```php

// GET Method
$Books = Rest::get('/books');

// POST Method
Rest::post('/books', array('title' => 'Your Book Title'));

// PUT Method
Rest::put('/books/1', array('title' => 'Your Book Title'));

// DELETE Method
Rest::delete('/books/1');

```

```
