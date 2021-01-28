<?php

namespace Biz\Search\Adapter;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\Exception\UnexpectedValueException;

class SearchAdapterFactory
{
    private static $cached = array();

    public static function create($type)
    {
        if (empty(self::$cached[$type])) {
            $type = ucfirst(str_replace('_', '', $type));
            $class = __NAMESPACE__."\\{$type}SearchAdapter";

            if (!file_exists(__DIR__."/{$type}SearchAdapter.php")) {
                throw new UnexpectedValueException("{$class} not found", 1);
            }
            $biz = ServiceKernel::instance()->getBiz();
            self::$cached[$type] = new $class($biz);
        }

        return self::$cached[$type];
    }
}
