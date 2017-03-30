<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\Exception\ApiNotFoundException;
use ApiBundle\Api\PathMeta;
use Codeages\Biz\Framework\Context\Biz;

class ResourceManager
{
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function create(PathMeta $meta)
    {
        $className = $meta->getResourceClassName();

        if (!class_exists($className)) {
            throw new ApiNotFoundException('API Resource Not found');
        }
        return new ResourceProxy(new $className($this->biz));
    }
}