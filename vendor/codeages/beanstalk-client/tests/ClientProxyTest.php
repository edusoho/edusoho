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
use Codeages\Beanstalk\ClientProxy;

class ClientProxyTest extends \PHPUnit_Framework_TestCase
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
    }

    public function testConnection()
    {
        $client = new ClientProxy($this->subject);

        $job = $client->put(0, 0, 10, 'hello!');
        $job = $client->put(0, 0, 10, 'hello!');

        $this->assertGreaterThan(0, $job);
    }
}
