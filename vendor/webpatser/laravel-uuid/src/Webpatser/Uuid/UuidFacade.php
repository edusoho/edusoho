<?php

namespace Webpatser\Uuid;

use Illuminate\Support\Facades\Facade;

/**
 * UuidFacade
 *
 */
class UuidFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'uuid';
    }
}
