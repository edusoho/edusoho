<?php

namespace Codeages\Biz\Framework\Targetlog\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Filesystem\Filesystem;

class LogReader
{
    public function __construct($cacheDirectory = null)
    {
        $this->cacheDirectory = $cacheDirectory;
        if ($cacheDirectory) {
            $fs = new Filesystem();
            $fs->mkdir($cacheDirectory);
        }
    }

    public function read($service)
    {
        $cache = $this->readCache($service);
        if ($cache) {
            return $cache;
        }
        $reflectClass = new \ReflectionClass($service);
        $interfaces = $reflectClass->getInterfaces();

        $interceptorData = $this->handleInterface($interfaces);

        $this->saveCache($service, $interceptorData);

        return $interceptorData;
    }

    protected function handleInterface($interfaces)
    {
        $annotationReader = new AnnotationReader();
        $annotationReader::addGlobalIgnoredName('before');
        $interceptorData = array();
        foreach ($interfaces as $interfaceName => $interfaceObj) {
            $reflectInterface = new \ReflectionClass($interfaceName);
            $methods = $reflectInterface->getMethods();
            $getInterceptorData = $this->getInterceptorData($annotationReader, $methods);
            if (!empty($getInterceptorData)) {
                $interceptorData = array_merge($interceptorData, $getInterceptorData);
            }
        }

        return $interceptorData;
    }

    protected function getInterceptorData($annotationReader, $methods)
    {
        $interceptorData = array();
        foreach ($methods as $method) {
            $annotation = $annotationReader->getMethodAnnotation($method, 'Codeages\Biz\Framework\TargetLog\Annotation\Log');
            if (empty($annotation)) {
                $interceptorData[$method->getName()] = array();
                continue;
            }
            $log = array(
                'level' => $annotation->getLevel(),
                'levelId' => $annotation->getLevelId(),
                'targetType' => $annotation->getTargetType(),
                'targetId' => $annotation->getTargetId(),
                'action' => $annotation->getAction(),
                'message' => $annotation->getMessage(),
            );
            $interceptorData[$method->getName()] = $log;
        }

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

        $metadata['cached_time'] = time();

        $filePath = $this->getCacheFilePath($this->cacheDirectory, $service);
        $content = "<?php \n return ".var_export($interceptorData, true).';';

        file_put_contents($filePath, $content);
    }

    protected function getCacheFilePath($cacheDirectory, $service)
    {
        $filename = str_replace('\\', '_', is_string($service) ? $service : get_class($service)).'.php';
        $filepath = $this->cacheDirectory.DIRECTORY_SEPARATOR.$filename;

        return $filepath;
    }
}
