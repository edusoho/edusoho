<?php

namespace OAuth2\ServerBundle\Tests\Entity;

use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Entity\AuthorizationCode;

class AuthorizationCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $container = ContainerLoader::buildTestContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $authcode = new AuthorizationCode();
        $authcode->setCode($code = 'test-code-'.rand());
        $authcode->setExpires(new \DateTime('+10 minutes')); // ten minutes from now
        $authcode->setRedirectUri('http://brentertainment.com');

        $em->persist($authcode);
        $em->flush();

        $stored = $em->find('OAuth2\ServerBundle\Entity\AuthorizationCode', array('code' => $code));

        $this->assertNotNull($stored);
        $this->assertEquals($code, $stored->getCode());
        $this->assertEquals($authcode->getExpires(), $stored->getExpires());
        $this->assertEquals($authcode->getRedirectUri(), $stored->getRedirectUri());
    }
}