<?php

namespace ApiBundle\Api\Util;

use AppBundle\Twig\WebExtension;

class AssetHelper
{
    /**
     * @var WebExtension
     */
    private static $webExtension;

    public static function getFurl($path, $defaultKey = false)
    {
        return self::$webExtension->getFurl($path, $defaultKey);
    }

    /**
     * @param WebExtension $webExtension
     */
    public static function setWebExtension(\Twig_Extension $webExtension)
    {
        self::$webExtension = $webExtension;
    }
}