<?php
namespace Custom\Service\Carts\Type;

class CartItemTypeFactory
{
    private static $cached = array();

    public static function create($type)
    {
        if (empty(self::$cached[$type])) {
            $type = ucfirst(str_replace('_', '', $type));
            $class = __NAMESPACE__  . "\\{$type}ItemType";
            self::$cached[$type] = new $class();
        }

        return self::$cached[$type];
    }
}