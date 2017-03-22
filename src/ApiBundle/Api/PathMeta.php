<?php

namespace ApiBundle\Api;

use ApiBundle\Api\Exception\BadRequestException;

class PathMeta
{
    private $httpMethod = '';

    private $resNames = array();

    private $slugs = array();

    private $singleMap = array(
        'GET' => 'get',
        'POST' => 'update',
        'DELETE' => 'remove'
    );

    private $listMap = array(
        'GET' => 'search',
        'POST' => 'add'
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