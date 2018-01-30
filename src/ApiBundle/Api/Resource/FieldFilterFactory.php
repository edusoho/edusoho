<?php

namespace ApiBundle\Api\Resource;

use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;

class FieldFilterFactory
{
    /**
     * @var CachedReader
     */
    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function createFilter($resource, $method)
    {
        $apiFilterAnnotation = $this->annotationReader->getMethodAnnotation(
            new \ReflectionMethod(get_class($resource), $method),
            'ApiBundle\Api\Annotation\ResponseFilter'
        );
        if ($apiFilterAnnotation && $apiFilterAnnotation->getClass() && class_exists($apiFilterAnnotation->getClass())) {
            $class = $apiFilterAnnotation->getClass();
            $fieldFilter = new $class();
            $mode = $apiFilterAnnotation->getMode();
            if ($mode) {
                $fieldFilter->setMode($mode);
            }

            return $fieldFilter;
        }

        $filterClass = get_class($resource).'Filter';
        if (class_exists($filterClass)) {
            return new $filterClass();
        }

        return null;
    }
}
