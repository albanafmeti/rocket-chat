Rocket-chat is a rest client package for Laravel that you can use to consume Rocket.Chat REST API.

## How to use

This package is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "noisim/rocket-chat": "dev-master"
    }
}
```

and run composer to update the dependencies `composer update`.

Then open your Laravel config file config/app.php and in the `$providers` array add the service provider for this package.

```php
\Noisim\RocketChat\RocketChatServiceProvider::class
```

Finally generate the configuration file running in the console:
```
php artisan vendor:publish --tag=config
```
