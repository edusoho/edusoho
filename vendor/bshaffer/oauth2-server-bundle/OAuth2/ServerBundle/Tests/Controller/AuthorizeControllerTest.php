<?php

namespace OAuth2\ServerBundle\Tests\Controller;

use OAuth2\HttpFoundationBridge\Request;
use OAuth2\ServerBundle\Tests\ContainerLoader;
use OAuth2\ServerBundle\Controller\AuthorizeController;

class AuthorizeControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testOpenIdConfig()
    {
        $container = ContainerLoader::buildTestContainer(array(
            __DIR__.'/../../vendor/symfony/symfony/src/Symfony/Bundle/SecurityBundle/Resources/config/security.xml',
        ));
        $controller = new AuthorizeController();
        $controller->setContainer($container);

        $clientManager = $container->get('oauth2.client_manager');

        $clientId = 'test-client-' . rand();
        $redirectUri = 'http://brentertainment.com';
        $scope  = 'openid';

        $clientManager->createClient(
          $clientId,
          explode(',', $redirectUri),
          array(),
          explode(',', $scope)
        );

        $request = new Request(array(
            'client_id'     => $clientId,
            'response_type' => 'code',
            'scope'         => 'openid',
            'state'         => 'xyz',
            'foo'           => 'bar',
            'nonce'         => '123',
        ));
        $container->set('oauth2.request', $request);

        $params = $controller->validateAuthorizeAction();

        $this->assertArrayHasKey('nonce', $params['qs'], 'optional included param');
        $this->assertArrayNotHasKey('foo', $params['qs'], 'invalid included param');
        $this->assertArrayNotHasKey('redirect_uri', $params['qs'], 'optional excluded param');

        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../Resources/views');
        $twig = new \Twig_Environment($loader);
        $template = $twig->loadTemplate('Authorize/authorize.html.twig');
        $html = $template->render($params);

        $this->assertContains(htmlentities(http_build_query($params['qs'])), $html);
    }
}