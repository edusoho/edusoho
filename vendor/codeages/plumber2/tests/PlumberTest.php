<?php

use Codeages\Plumber\Plumber;

class PlumberTest extends \PHPUnit\Framework\TestCase
{
    public function testStart()
    {
        $options = [
            'workers' => [
                [
                    'class' => 'Codeages\Plumber\Example\Example1Worker',
                    'numn' => 1,
                    'queue' => 'default',
                    'tube' => 'test',
                ],
            ],

            'queues' => [
                'default' => [
                    'type' => 'redis',
                    'host' => '127.0.0.1',
                    'port' => '6679',
                ],
            ],
        ];

        $plumber = new Plumber($options);
    }
}
