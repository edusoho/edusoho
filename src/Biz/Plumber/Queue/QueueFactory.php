<?php

namespace Biz\Plumber\Queue;

use AppBundle\Common\Exception\UnexpectedValueException;

class QueueFactory
{
    private static $map = [
        'beanstalk' => 'Biz\Plumber\Queue\BeanstalkQueue',
        'redis' => 'Biz\Plumber\Queue\RedisQueue',
    ];

    public static function create($type = 'beanstalk', $config)
    {
        if (empty(self::$map[$type])) {
            throw new UnexpectedValueException('Queue type can not be found.');
        }

        return new self::$map[$type]($config);
    }
}
