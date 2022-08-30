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

    public static function transformImages($content)
    {
        preg_match_all('/<img.*?src=[\"|\']?(.*?)[\"|\']*?\/?\s*>/i', $content, $matches);
        if (empty($matches)) {
            return $content;
        }
        $imgList = [];
        foreach ($matches[1] as $imgUrl) {
            $imgList[] = AssetHelper::uriForPath($imgUrl);
        }
        return str_replace($matches[1], $imgList, $content);
    }

    public static function transformImagesAddUrl($content, $type)
    {
        if ($type == 'picture'){
            preg_match_all('/\/files/i', $content, $matches);
        }else{
            preg_match_all("/public:\//i", $content, $matches);
        }
        return str_replace($matches[0], AssetHelper::uriForPath($matches[1]), $content);
    }
}
