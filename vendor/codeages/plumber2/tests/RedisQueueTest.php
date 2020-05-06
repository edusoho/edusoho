<?php

class RedisQueueTest extends \PHPUnit\Framework\TestCase
{
    public function testPop()
    {
        $options = [
            'host' => '127.0.0.1',
            'port' => '6379',
        ];

        $q = new \Codeages\Plumber\RedisQueue($options);
        $q->clear('test');

        $m = $q->pop('test');
        $this->assertNull($m);

        $m1 = 'message_1';
        $m2 = 'message_2';
        $m3 = 'message_3';

        $q->push('test', $m1);
        $q->push('test', $m2);
        $q->push('test', $m3);

        $pm1 = $q->pop('test');
        $pm2 = $q->pop('test');
        $pm3 = $q->pop('test');

        $this->assertEquals($m1, $pm1);
        $this->assertEquals($m2, $pm2);
        $this->assertEquals($m3, $pm3);
    }

    public function testPop_Blocking()
    {
        $options = [
            'host' => '127.0.0.1',
            'port' => '6379',
        ];

        $q = new \Codeages\Plumber\RedisQueue($options);
        $q->clear('test');

        $pm = $q->pop('test', true, 1);
        $this->assertNull($pm);

        $m1 = 'message_1';
        $q->push('test', $m1);
        $pm1 = $q->pop('test', true, 1);
        $this->assertEquals($m1, $pm1);
    }
}
