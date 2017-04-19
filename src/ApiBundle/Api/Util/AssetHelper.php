<?php

namespace ApiBundle\Api\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AssetHelper
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    public static function getFurl($path, $defaultKey = false)
    {
        return self::$container->get('web.twig.extension')->getFurl($path, $defaultKey);
    }

    public static function uriForPath($path)
    {
        return self::$container->get('request')->getUriForPath($path);
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }
}