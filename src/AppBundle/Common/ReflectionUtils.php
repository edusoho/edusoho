<?php

namespace AppBundle\Common;

use Codeages\Biz\Framework\Service\ServiceProxy;

/**
 * 使用方法可参考　ReflectionUtilsTest.php
 */
class ReflectionUtils
{
    /**
     * 通过反射的方式调用方法, 可调用私有方法
     * 如
     * ReflectionUtils::invokeMethod($this->getUsersSyncService(), 'dealData', array($arg1, $arg2));
     */
    public static function invokeMethod($classObj, $methodName, $args = null)
    {
        if ($classObj instanceof ServiceProxy) {
            $classPackName = $classObj->getClassName();
            $classObj = $classObj->getClass();
        } else {
            $classPackName = get_class($classObj);
        }

        $class = new \ReflectionClass($classPackName);
        $method = $class->getmethod($methodName);
        $method->setAccessible(true);

        if (isset($args)) {
            $result = $method->invokeArgs($classObj, $args);
        } else {
            $result = $method->invoke($classObj);
        }

        return $result;
    }

    /**
     * 通过反射的方式, 设置非静态局部变量
     * 如
     * ReflectionUtils::setProperty(ServiceKernel::instance(), 'debug', true);
     */
    public static function setProperty($classObj, $propertyName, $propertyValue)
    {
        if ($classObj instanceof ServiceProxy) {
            $classPackName = $classObj->getClassName();
            $classObj = $classObj->getClass();
        } else {
            $classPackName = get_class($classObj);
        }
        $class = new \ReflectionClass($classPackName);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($classObj, $propertyValue);

        return $classObj;
    }

    public static function getProperty($classObj, $propertyName)
    {
        if ($classObj instanceof ServiceProxy) {
            $classPackName = $classObj->getClassName();
            $classObj = $classObj->getClass();
        } else {
            $classPackName = get_class($classObj);
        }
        $class = new \ReflectionClass($classPackName);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($classObj);
    }

    /**
     * 通过反射的方式, 设置静态局部变量
     * 如
     * ReflectionUtils::setStaticProperty(ServiceKernel::instance(), '_instance', null);
     */
    public static function setStaticProperty($classObj, $propertyName, $propertyValue)
    {
        if ($classObj instanceof ServiceProxy) {
            $classPackName = $classObj->getClassName();
            $classObj = $classObj->getClass();
        } else {
            $classPackName = get_class($classObj);
        }
        $class = new \ReflectionClass($classPackName);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($propertyValue);
    }
}
