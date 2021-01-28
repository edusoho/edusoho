<?php
/**
 * beanstalk: A minimalistic PHP beanstalk client.
 *
 * Copyright (c) 2009-2015 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Codeages\Beanstalk\Tests;

use Codeages\Beanstalk\Client;
use Codeages\Beanstalk\Exception\ServerException;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public $subject;

    protected function setUp()
    {
        $host = getenv('TEST_BEANSTALKD_HOST');
        $port = getenv('TEST_BEANSTALKD_PORT');

        if (!$host || !$port) {
            $message = 'TEST_BEANSTALKD_HOST and/or TEST_BEANSTALKD_PORT env variables not defined.';
            $this->markTestSkipped($message);
        }
        $this->subject = new Client(compact('host', 'port'));

        if (!$this->subject->connect()) {
            $message = "Need a running beanstalkd server at {$host}:{$port}.";
            $this->markTestSkipped($message);
        }

        foreach ($this->subject->listTubes() as $tube) {
            $this->subject->useTube($tube);

            while ($job = $this->subject->peekReady()) {
                $this->subject->delete($job['id']);
            }
            while ($job = $this->subject->peekBuried()) {
                $this->subject->delete($job['id']);
            }
        }
        $this->subject->useTube('default');
    }

    public function testPut()
    {
        $this->subject->useTube('test');

        $this->subject->put(0, 0, 100, 'test');
        $this->subject->put(0, 0, 100, 'test');

        $result = $this->subject->statsTube('test');
        $this->assertEquals(2, $result['current-jobs-urgent']);
    }

    public function testUseTube()
    {
        $result = $this->subject->useTube('test0');
        $this->assertEquals('test0', $result);

        $result = $this->subject->useTube('test1');
        $this->assertEquals('test1', $result);
    }

    public function testReserveWithoutTimeout()
    {
        $this->subject->put(0, 0, 100, 'test0');

        $result = $this->subject->reserve();
        $this->assertEquals('test0', $result['body']);
    }

    public function testReserveWithTimeout()
    {
        $start = microtime(true);

        $this->subject->reserve(1);

        $result = microtime(true)  - $start;
        $this->assertEquals(1, (integer) $result);
    }

    public function testExceedFreadDefaultChunkSize()
    {
        $this->subject->put(0, 0, 100, str_repeat('0', 8192 + 4));
        $result = $this->subject->reserve(1);

        $this->subject->delete($result['id']);
        $this->assertEquals(8192 + 4, strlen($result['body']));

        $this->subject->put(0, 0, 100, str_repeat('0', 8192 * 4));
        $result = $this->subject->reserve(1);

        $this->subject->delete($result['id']);
        $this->assertEquals(8192 * 4, strlen($result['body']));
    }

    public function testExceedMaxJobSize()
    {
        $this->subject->put(0, 0, 100, str_repeat('0', 65536 - 1));
        $result = $this->subject->reserve(5);

        $this->subject->delete($result['id']);
        $this->assertEquals(65536 - 1, strlen($result['body']));

        try {
            $this->subject->put(0, 0, 100, str_repeat('0', 65536));
        } catch (ServerException $e) {
            $this->assertStringEndsWith('JOB_TOO_BIG', $e->getMessage());
        }

        $result = $this->subject->reserve(1);
        $this->assertFalse($result);
    }

    public function testHighFrequencyPut()
    {
        $this->subject->useTube('test');

        for ($i = 0; $i < 10000; ++$i) {
            $this->subject->put(0, 0, 100, 'test'.$i);
        }
        $result = $this->subject->statsTube('test');
        $this->assertEquals(10000, $result['current-jobs-urgent']);
    }
}
