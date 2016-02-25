<?php

namespace Topxia\Service\Common\Annotations;

interface IAnnotation
{
    /**
     * @param  [Object] service impl
     * @param  [string] method name
     * @param  [array] arguments
     * @return [null]
     */
    public function invoke($obj, $method, $arguments);
}
