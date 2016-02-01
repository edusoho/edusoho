<?php

namespace Topxia\Service\Common\Annotations;

/**
 * Annotation class for @Route().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Test implements IAnnotation
{
    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters.
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        var_dump($data);
    }

    public function invoke($obj, $method, $arguments)
    {
        echo get_class($obj)."\n";
        echo $method."\n";
        var_dump($arguments);
    }
}
