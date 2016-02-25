<?php

namespace Topxia\Service\Common\Proxy\Tests;

use Topxia\Service\Common\Annotations\Annotation;
use Topxia\Service\Common\Annotations\IAnnotation;

/**
 * Annotation class for @TestAnnotation().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 */
class TestAnnotation extends Annotation implements IAnnotation
{
    public function invoke($obj, $method, $arguments)
    {
        echo 'run annotation!';
    }
}
