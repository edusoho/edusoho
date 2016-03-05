<?php
namespace Topxia\Service\Search\Adapter;

class SearchAdapterFactory
{
    private static $cached = array();

    public static function create($type)
    {
        if (empty(self::$cached[$type])) {
            $type  = ucfirst(str_replace('_', '', $type));
            $class = __NAMESPACE__."\\{$type}SearchAdapter";

            if (!file_exists(__DIR__."/{$type}SearchAdapter.php")) {
                throw new \RuntimeException("{$class} not found", 1);
            }

            self::$cached[$type] = new $class();
        }

        return self::$cached[$type];
    }
}
