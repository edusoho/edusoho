<?php

namespace ApiBundle\Api;

use ApiBundle\Api\Exception\BadRequestException;
use ApiBundle\Api\Resource\Resource;

class PathMeta
{
    private $httpMethod = '';

    private $resNames = array();

    private $slugs = array();

    private $singleMap = array(
        'GET' => Resource::METHOD_GET,
        'POST' => Resource::METHOD_UPDATE,
        'DELETE' => Resource::METHOD_REMOVE
    );

    private $listMap = array(
        'GET' => Resource::METHOD_SEARCH,
        'POST' => Resource::METHOD_ADD
    );

    public function getQualifiedResName()
    {
        if (empty($this->resNames) || empty($this->resNames[0])) {
            throw new BadRequestException('URL is not supported');
        }

        $QualifiedResName = $this->convert($this->resNames[0]).'\\';
        foreach ($this->resNames as $resName) {
            $QualifiedResName .= $this->convert($resName);
        }

        return $QualifiedResName;
    }

    public function getResMethod()
    {
        if (count($this->resNames) == count($this->slugs)) {
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

    private function convert($string)
    {
        $result = '';
        $words = explode('_', $string);
        foreach ($words as $word) {
            $result .= ucfirst(rtrim($word, 's'));
        }

        return $result;
    }
}