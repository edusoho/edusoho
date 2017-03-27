<?php

namespace OAuth2\ServerBundle\Tests\Entity;

use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Entity\ClientPublicKey;
use OAuth2\ServerBundle\Entity\Client;

class ClientPublicKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $container = ContainerLoader::buildTestContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $client = new Client();
        $client->setClientId($token = 'test-client-'.rand());
        $client->setClientSecret('very-secure');
        $client->setRedirectUri(array('http://brentertainment.com'));

        $em->persist($client);
        $em->flush();

        $public_key = new ClientPublicKey();
        $public_key->setClient($client);

        // create and set the public key
        $res = openssl_pkey_new();

        // Extract the public key from $res to $pubKey
        $pubKeyDetails = openssl_pkey_get_details($res);
        $pubKey = $pubKeyDetails['key'];
        $public_key->setPublicKey($pubKey);

        $em->persist($public_key);
        $em->flush();

        // test direct access
        $stored = $em->find('OAuth2\ServerBundle\Entity\ClientPublicKey', array('client_id' => $client->getClientId()));

        $this->assertNotNull($stored);
        $this->assertEquals($pubKey, $stored->getPublicKey());
    }
}