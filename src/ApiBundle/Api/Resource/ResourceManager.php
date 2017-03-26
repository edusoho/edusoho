<?php

namespace ApiBundle\Api\Resource;

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
        return new ResourceProxy(new $className($this->biz));
    }
}