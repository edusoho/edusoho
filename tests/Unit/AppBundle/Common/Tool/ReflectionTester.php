<?php

namespace Tests\Unit\AppBundle\Common\Tool;

class ReflectionTester
{
    private $ok;
    private static $staticAttr;

    public static function getStaticAttr()
    {
        return self::$staticAttr;
    }

    public function getOk()
    {
        return $this->ok;
    }

    public function setOk($ok)
    {
        $this->ok = $ok;
    }

    protected function getHello($param1, $param2)
    {
        return 'hello'.'_'.$param1.'_'.$param2;
    }
}
