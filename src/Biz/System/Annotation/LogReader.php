<?php

namespace Biz\System\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Filesystem\Filesystem;

class LogReader
{
    public function __construct($cacheDirectory = null)
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->initCacheDir();
    }

    public function read($service)
    {
        $cache = $this->readCache($service);
        if ($cache) {
            return $cache;
        }
        $annotationReader = new AnnotationReader();
        $annotationReader::addGlobalIgnoredName('before');
        $reflectClass = new \ReflectionClass($service);
        $interfaces = $reflectClass->getInterfaces();
        $interceptorData = array();

        foreach ($interfaces as $interfaceName => $interfaceObj) {
            $reflectInterface = new \ReflectionClass($interfaceName);
            $methods = $reflectInterface->getMethods();
            $nameSpaceKey = self::getServiceNameSpaceKey($reflectInterface->getNamespaceName());
            $name = self::getServiceName($interfaceName);
            foreach ($methods as $method) {
                $annotation = $annotationReader->getMethodAnnotation($method, '\Biz\System\Annotation\Log');

                $ReflectionFunc = new \ReflectionMethod($interfaceName, $method->name);
                $parameters = $ReflectionFunc->getParameters();
                $funcParam = array();
                foreach ($parameters as $parameter) {
                    $funcParam[] = $parameter->name;
                }

                if (empty($annotation)) {
                    $interceptorData[$method->getName()] = array();
                    continue;
                }
                $log = array();
                $log['module'] = $annotation->getModule();
                $log['action'] = $annotation->getAction();
                $log['param'] = $annotation->getParam();
                $log['postfix'] = $annotation->getPostfix();
                $log['funcName'] = $annotation->getFuncName();
                $log['funcParam'] = $funcParam;
                $serviceName = $annotation->getServiceName();
                if (!empty($serviceName)) {
                    $log['service'] = $serviceName;
                } else {
                    $log['service'] = $nameSpaceKey.':'.$name;
                }
                $interceptorData[$method->getName()] = $log;
            }
        }

        $this->saveCache($service, $interceptorData);

        return $interceptorData;
    }

    protected function readCache($service)
    {
        if (!$this->cacheDirectory) {
            return null;
        }

        $filePath = $this->getCacheFilePath($this->cacheDirectory, $service);
        if (file_exists($filePath)) {
            return include $filePath;
        }

        return null;
    }

    protected function saveCache($service, $interceptorData)
    {
        if (!$this->cacheDirectory) {
            return;
        }
        $this->initCacheDir();

        $metadata['cached_time'] = time();

        $filePath = $this->getCacheFilePath($this->cacheDirectory, $service);
        $content = "<?php \n return ".var_export($interceptorData, true).';';

        file_put_contents($filePath, $content);
    }

    protected function initCacheDir()
    {
        $cacheDirectory = $this->cacheDirectory;
        if ($cacheDirectory && !is_dir($cacheDirectory)) {
            $fs = new Filesystem();
            $fs->mkdir($cacheDirectory);
        }
    }

    protected function getCacheFilePath($cacheDirectory, $service)
    {
        $filename = str_replace('\\', '_', is_string($service) ? $service : get_class($service)).'.php';
        $filepath = $this->cacheDirectory.DIRECTORY_SEPARATOR.$filename;

        return $filepath;
    }

    protected function getServiceNameSpaceKey($nameSpace)
    {
        $array = explode('\\', $nameSpace);
        foreach ($array as $key => $value) {
            if ('Service' == $value || 'Biz' == $value) {
                unset($array[$key]);
            }
        }

        return implode(':', $array);
    }

    protected function getServiceName($name)
    {
        $array = explode('\\', $name);
        $count = count($array);
        if ($count > 1) {
            return $array[$count - 1];
        }

        return $name;
    }
}
