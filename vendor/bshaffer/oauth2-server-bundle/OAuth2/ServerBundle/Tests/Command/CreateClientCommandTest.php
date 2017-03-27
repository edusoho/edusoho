<?php

namespace OAuth2\ServerBundle\Tests\Command;

use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Command\CreateClientCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CreateClientCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateClientWithInvalidScope()
    {
        $container = ContainerLoader::buildTestContainer();
        $command = new CreateClientCommand();
        $command->setContainer($container);

        $client_id    = 'Client-ID-'.rand();
        $redirectUris = 'http://brentertainment.com';
        $grantTypes   = 'authorization_code,client_credentials';
        $scopes		  = 'fakescope';

        $input = new ArgvInput(array('command', $client_id, $redirectUris, $grantTypes, $scopes));
        $output = new BufferedOutput();

        $statusCode = $command->run($input, $output);

        $this->assertEquals(1, $statusCode);
        $this->assertTrue(false !== strpos($output->fetch(), 'Scope not found, please create it first'));
    }

    public function testCreateClient()
    {
        $container = ContainerLoader::buildTestContainer();
        $command = new CreateClientCommand();
        $command->setContainer($container);

        $client_id    = 'Client-ID-'.rand();
        $redirectUris = 'http://brentertainment.com';
        $grantTypes   = 'authorization_code,client_credentials';
        $scope        = 'scope1';

        // ensure the scope exists
        $scopeStorage = $container->get('oauth2.storage.scope');
        if (!$scopeStorage->scopeExists($scope)) {
            $scopeManager = $container->get('oauth2.scope_manager');
            $scopeManager->createScope($scope, 'test scope');
        }

        $input = new ArgvInput(array('command', $client_id, $redirectUris, $grantTypes, $scope));
        $output = new BufferedOutput();

        $statusCode = $command->run($input, $output);

        $this->assertEquals(0, $statusCode, $output->fetch());

        // verify client details have been stored
        $storage = $container->get('oauth2.storage.client_credentials');
        $client  = $storage->getClientDetails($client_id);

        $this->assertNotNull($client);
        $this->assertEquals($redirectUris, $client['redirect_uri']);
        $this->assertEquals(explode(',', $grantTypes), $client['grant_types']);

        // verify client scope has been stored
        $clientScope = $storage->getClientScope($client_id);
        $this->assertEquals($scope, $clientScope);
    }
}