<?php

namespace OAuth2\ServerBundle\Tests\Entity;

use OAuth2\ServerBundle\Manager\ScopeManager;
use OAuth2\ServerBundle\Tests\ContainerLoader;

class ScopeManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testFindScopesByScopes()
    {
        $container = ContainerLoader::buildTestContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $manager = new ScopeManager($em);

        $scopes = array('test-scope-'.rand(), 'test-scope-'.rand(), 'test-scope-'.rand());

        foreach ($scopes as $scope) {
            $manager->createScope($scope, $scope);
        }

        $dbScopes = $manager->findScopesByScopes($scopes);

        $this->assertNotNull($dbScopes);
        $this->assertEquals(count($dbScopes), count($scopes));
    }
}