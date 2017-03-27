<?php

namespace OAuth2\ServerBundle\Tests\Entity;

use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Entity\AccessToken;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $container = ContainerLoader::buildTestContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $access_token = new AccessToken();
        $access_token->setToken($token = 'test-token-'.rand());
        $access_token->setExpires(new \DateTime('+10 minutes')); // ten minutes from now

        $em->persist($access_token);
        $em->flush();

        $stored = $em->find('OAuth2\ServerBundle\Entity\AccessToken', array('token' => $token));

        $this->assertNotNull($stored);
        $this->assertEquals($token, $stored->getToken());
        $this->assertEquals($access_token->getExpires(), $stored->getExpires());
    }
}