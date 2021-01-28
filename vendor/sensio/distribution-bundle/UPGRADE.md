UPGRADE FROM 4.0 to 5.0
=======================

 * The web configurator got removed. So you need to remove the `_configurator`
   routing entry from `app/config/routing_dev.yml`.

 * The generated `app/bootstrap.php.cache` does not include autoloading anymore.
   So you need to add the autoloading code in your front controllers `web/app.php`,
   `web/app_dev.php`, `app/console` and `app/phpunit.xml.dist` (bootstrap config).

 * If you have been using the Symfony 3 directory structure already, you need to
   overwrite the cache and log directories in your `AppKernel` as it is also done
   in Symfony 3 now (see
   [`app/AppKernel.php`](https://github.com/symfony/symfony-standard/blob/3.3/app/AppKernel.php#L40-L48)).
