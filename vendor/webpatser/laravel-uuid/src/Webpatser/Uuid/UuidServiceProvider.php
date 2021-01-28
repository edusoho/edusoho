<?php
namespace Webpatser\Uuid;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class UuidServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('uuid', function ($attribute, $value, $parameters, $validator) {
            return Uuid::validate($value);
        });
    }
}
