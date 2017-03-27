<?php

namespace OAuth2\ServerBundle\Tests\Entity;

use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Entity\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $container = ContainerLoader::buildTestContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $client = new Client();
        $client->setClientId($client_id = 'This Is My Client ID '.rand());
        $client->setClientSecret('very-secure');
        $client->setRedirectUri(array('http://brentertainment.com'));

        $em->persist($client);
        $em->flush();

        $stored = $em->find('OAuth2\ServerBundle\Entity\Client', array('client_id' => $client_id));

        $this->assertNotNull($stored);
        $this->assertEquals($client_id, $stored->getClientId());
        $this->assertEquals($client->getClientSecret(), $stored->getClientSecret());
        $this->assertEquals($client->getRedirectUri(), $stored->getRedirectUri());
    }
}