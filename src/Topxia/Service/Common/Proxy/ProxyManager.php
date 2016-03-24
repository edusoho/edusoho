<?php

namespace Topxia\Service\Common\Proxy;

/**
 * 代理管理.
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
        return new ProxyFramework(new $className());
    }
}
