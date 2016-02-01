<?php

namespace Topxia\Service\Common\Proxy;

/**
 * 代理管理.
 * 思考:单例OR静态？
 */
class ProxyManager
{
    /**
     * 创建类代理.
     *
     * @param type $className
     */
    public static function create($className)
    {
        static $objectPools = array();
        if (!isset($objectPools[$className])) {
            $objectPools[$className] = new ProxyFramework(new $className());
        }

        return $objectPools[$className];
    }
}
