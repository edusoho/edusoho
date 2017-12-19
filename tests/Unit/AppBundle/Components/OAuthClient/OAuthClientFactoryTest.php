<?php

namespace Tests\Unit\AppBundle\Components\OAuthClient;

use Biz\BaseTestCase;
use AppBundle\Components\OAuthClient\OAuthClientFactory;

class OAuthClientFactoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $clazz = OAuthClientFactory::create('qq', array('key' => 'key', 'secret' => 'secret'));
        $this->assertEquals('AppBundle\Component\OAuthClient\QqOAuthClient', get_class($clazz));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithNonExistSecret()
    {
        OAuthClientFactory::create('qq', array('key' => 'key'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithNonExistType()
    {
        OAuthClientFactory::create('dsd', array('key' => 'key', 'secret' => 'secret'));
    }
}
