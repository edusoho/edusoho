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
        return self::$container->get('request_stack')->getMasterRequest()->getUriForPath($path);
    }

    public static function getScheme()
    {
        return self::$container->get('request_stack')->getMasterRequest()->getScheme();
    }

    public static function callAppExtensionMethod($method, $params)
    {
        return call_user_func_array(array(self::$container->get('web.twig.app_extension'), $method), $params);
    }

    public static function renderView($view, array $parameters = array())
    {
        return self::$container->get('templating')->render($view, $parameters);
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }
}
