<?php

namespace Codeages\Biz\Framework\Targetlog\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Log
{
    public function __construct($desc = 'no desc')
    {
        $this->desc = $desc;
    }
}
