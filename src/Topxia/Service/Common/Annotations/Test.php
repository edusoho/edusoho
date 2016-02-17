<?php

namespace Topxia\Service\Common\Annotations;

/**
 * Annotation class for @Test().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 */
class Test extends Annotation implements IAnnotation
{
    public function invoke($obj, $method, $arguments)
    {
        echo get_class($obj)."\n";
        echo $method."\n";
        var_dump($arguments);
    }
}
