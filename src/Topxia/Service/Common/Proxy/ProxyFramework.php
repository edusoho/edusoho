<?php

namespace Topxia\Service\Common\Proxy;

use Topxia\Service\Common\Annotations\Loader\AnnotationsLoader;
/**
 * 代理框架.
 */
class ProxyFramework
{
    private $object = null;

    private $annotations = array();
    /**
     * 类代理实例.
     *
     * @param type $object
     */
    public function __construct($object)
    {
        $this->object = $object;
        $this->annotations = AnnotationsLoader::load(get_class($object));
    }
    /**
     * 获取类变量.
     *
     * @param type $name
     */
    public function __get($name)
    {
        return $this->object->$name;
    }
    /**
     * 设置类变量.
     *
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value)
    {
        return $this->object->$name = $value;
    }
    /**
     * 调用类方法.
     * 目前的注解只实现了方法前调用，后期可以通过注解传参的方式实现方法后调用
     *
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->annotations)) {
            $annot = $this->annotations[$name];
            $annot->invoke($this->object, $name, $arguments);
        }

        return call_user_func_array(array($this->object, $name), $arguments);
    }
}
