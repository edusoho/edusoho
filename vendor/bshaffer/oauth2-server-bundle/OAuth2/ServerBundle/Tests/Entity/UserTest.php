<?php

namespace OAuth2\ServerBundle\Tests\Entity;

use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $container = ContainerLoader::buildTestContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $user = new User();
        $user->setUsername($name = 'test-user-'.rand());
        $user->setPassword('very-secure');
        $user->setSalt(sha1(time()));

        $em->persist($user);
        $em->flush();

        $stored = $em->find('OAuth2\ServerBundle\Entity\User', array('username' => $name));

        $this->assertNotNull($stored);
        $this->assertEquals($name, $stored->getUsername());
        $this->assertEquals($user->getPassword(), $stored->getPassword());
        $this->assertEquals($user->getSalt(), $stored->getSalt());
    }
}