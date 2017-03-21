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
        $qualifiedResName = $meta->getQualifiedResName();
        $className = __NAMESPACE__ .'\\'.$qualifiedResName;
        return new $className($this->biz);
    }
}