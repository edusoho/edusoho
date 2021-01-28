<?php
namespace Docopt\Test;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $usage
     * @param string[]|string $args
     */
    protected function docopt($usage, $args='', $extra=array())
    {
        $extra = array_merge(array('exit'=>false, 'help'=>false), $extra);
        $handler = new \Docopt\Handler($extra);
        return call_user_func(array($handler, 'handle'), $usage, $args);
    }
}
