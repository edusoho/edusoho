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
        $this->object      = $object;
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
     * 通过注解传入aspect参数控制注解的执行顺序，默认是before
     * before:方法前执行
     * around:将方法的执行交给注解类处理，用了该参数的方法只能使用一个注解
     * after:方法后执行
     *
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->annotations)) {
            $annot = $this->annotations[$name];

            $aspect = $annot->getAspect();

            switch ($aspect) {
                case 'before':
                    $annot->invoke($this->object, $name, $arguments);
                    return call_user_func_array(array($this->object, $name), $arguments);
                case 'around':
                    return $annot->invoke($this->object, $name, $arguments);
                case 'after':
                    $result = call_user_func_array(array($this->object, $name), $arguments);
                    $annot->invoke($this->object, $name, $arguments);
                    return $result;
                default:
                    break;
            }
        }

        return call_user_func_array(array($this->object, $name), $arguments);
    }
}
