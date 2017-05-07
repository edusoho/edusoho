<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Security\Authentication\Token\ApiToken;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class OAuth2AuthenticationListener implements ListenerInterface
{
    private $container;
    private $tokenStorage;
    private $biz;

    public function __construct(ContainerInterface $container)
    {
        $this->tokenStorage = $container->get('security.token_storage');
        $this->biz = $container->get('biz');
        $this->container = $container;
    }

    public function handle(Request $request)
    {
        if (null !== $this->tokenStorage->getToken()) {
            return;
        }

        $oauthRequest = \OAuth2\HttpFoundationBridge\Request::createFromRequest($request);
        if ($this->isOAuth2VerifyPass($oauthRequest)) {
            $tokenData = $this->getOAuth2Sever()->getAccessTokenData($oauthRequest);
            $userId = $tokenData['user_id'];
            $user = $this->getUserService()->getUser($userId);
            $token = $this->createToken($user, $request->getClientIp());
            $this->tokenStorage->setToken($token);
        }
    }

    private function isOAuth2VerifyPass($oauthRequest)
    {
        return $this->getOAuth2Sever()->verifyResourceRequest($oauthRequest);
    }

    private function getOAuth2Sever()
    {
        return $this->container->get('oauth2.server');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    private function createToken($user, $clientIp)
    {
        $currentUser = new CurrentUser();
        $user['currentIp'] = $clientIp;
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));

        return new ApiToken($currentUser, $currentUser->getRoles());
    }
}