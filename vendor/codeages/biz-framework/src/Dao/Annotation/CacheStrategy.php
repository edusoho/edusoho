<?php

namespace Codeages\Biz\Framework\Dao\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class CacheStrategy
{
    private $name;

    public function __construct(array $data)
    {
        $this->name = $data['value'];
    }

    public function getName()
    {
        return $this->name;
    }
}
