<?php

namespace Topxia\Service\Common\Proxy\Tests;

use Topxia\Service\Common\Proxy\Tests\TestAnnotation;

class TestClass
{
    /**
     * @TestAnnotation(aspect="before")
     */
    public function before()
    {
        echo 'run before method!';
    }

    /**
     * @TestAnnotation(aspect="after")
     */
    public function after()
    {
        echo 'run after method!';
    }

    /**
     * @AroundAnnotation(aspect="around")
     */
    public function around()
    {
        echo 'run around method!';
    }
    
}
