<?php

namespace Topxia\Service\Common\Proxy\Tests;

use Topxia\Service\Common\Annotations\Annotation;
use Topxia\Service\Common\Annotations\IAnnotation;

/**
 * Annotation class for @AroundAnnotation().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 */
class AroundAnnotation extends Annotation implements IAnnotation
{
    public function invoke($obj, $method, $arguments)
    {
        echo 'before run method!';
        $result = call_user_func_array(array($obj, $method), $arguments);
        echo 'after run method!';
        return $result;
    }
}
