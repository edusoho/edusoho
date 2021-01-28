<?php

namespace OAuth2\ServerBundle\Tests\Entity;

use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Entity\RefreshToken;

class RefreshTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $container = ContainerLoader::buildTestContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $refresh_token = new RefreshToken();
        $refresh_token->setToken($token = 'test-token-'.rand());
        $refresh_token->setExpires(new \DateTime('+10 minutes')); // ten minutes from now

        $em->persist($refresh_token);
        $em->flush();

        $stored = $em->find('OAuth2\ServerBundle\Entity\RefreshToken', array('token' => $token));

        $this->assertNotNull($stored);
        $this->assertEquals($token, $stored->getToken());
        $this->assertEquals($refresh_token->getExpires(), $stored->getExpires());
    }
}