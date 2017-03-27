<?php

namespace OAuth2\ServerBundle\Tests\Entity;

use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Entity\Scope;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $container = ContainerLoader::buildTestContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $scope = new Scope();
        $scope->setScope($name = 'test-scope-'.rand());
        $scope->setDescription('A Scope for Testing');

        $em->persist($scope);
        $em->flush();

        $stored = $em->find('OAuth2\ServerBundle\Entity\Scope', array('scope' => $name));

        $this->assertNotNull($stored);
        $this->assertEquals($name, $stored->getScope());
        $this->assertEquals($scope->getDescription(), $stored->getDescription());
    }
}