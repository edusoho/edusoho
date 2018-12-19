<?php

namespace ApiBundle\Api;

use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Doctrine\Common\Inflector\Inflector;

class PathMeta
{
    private $httpMethod = '';

    private $resNames = array();

    private $slugs = array();

    private $singleMap = array(
        'GET' => AbstractResource::METHOD_GET,
        'PATCH' => AbstractResource::METHOD_UPDATE,
        'PUT' => AbstractResource::METHOD_UPDATE,
        'DELETE' => AbstractResource::METHOD_REMOVE,
    );

    private $listMap = array(
        'GET' => AbstractResource::METHOD_SEARCH,
        'POST' => AbstractResource::METHOD_ADD,
        'DELETE' => AbstractResource::METHOD_REMOVE,
    );

    public function getResourceClassName()
    {
        if (empty($this->resNames) || empty($this->resNames[0])) {
            throw CommonException::NOTFOUND_API();
        }

        if ('plugins' == $this->resNames[0]) {
            return $this->getPluginResClass();
        } else {
            return $this->getNormalResClass(__NAMESPACE__);
        }
    }

    public function fallbackToCustomApi($customApiNamespaces)
    {
        $result = array(
            'isFind' => false,
            'className' => '',
        );
        foreach ($customApiNamespaces as $namespace) {
            $className = $this->getNormalResClass($namespace);
            if (class_exists($className)) {
                $result['isFind'] = true;
                $result['className'] = $className;
                break;
            }
        }

        return $result;
    }

    public function getResMethod()
    {
        $isSingleMethod = ('me' == $this->resNames[0] && count($this->resNames) - 1 == count($this->slugs)) || (count($this->resNames) == count($this->slugs));
        if ($isSingleMethod) {
            return $this->singleMap[$this->httpMethod];
        } else {
            return $this->listMap[$this->httpMethod];
        }
    }

    public function getSlugs()
    {
        return $this->slugs;
    }

    public function addResName($resName)
    {
        $this->resNames[] = $resName;
    }

    public function addSlug($slug)
    {
        $this->slugs[] = $slug;
    }

    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = strtoupper($httpMethod);
    }

    private function getNormalResClass($namespace)
    {
        $qualifiedResName = $this->convertToSingular($this->resNames[0]).'\\';

        foreach ($this->resNames as $resName) {
            $qualifiedResName .= $this->convertToSingular($resName);
        }

        return $namespace.'\\Resource\\'.$qualifiedResName;
    }

    private function getPluginResClass()
    {
        $resClassName = $this->convertToSingular($this->slugs[0]).'Plugin\\Api\\Resource\\';
        //去除/plugins/{pluginName}这部分url
        array_splice($this->slugs, 0, 1);
        array_splice($this->resNames, 0, 1);

        $resClassName .= $this->convertToSingular($this->resNames[0]).'\\';
        foreach ($this->resNames as $resName) {
            $resClassName .= $this->convertToSingular($resName);
        }

        return $resClassName;
    }

    private function convertToSingular($string)
    {
        return Inflector::singularize(Inflector::classify($string));
    }
}
