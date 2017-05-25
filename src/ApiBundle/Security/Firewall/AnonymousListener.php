<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Security\Firewall;

use ApiBundle\Security\Authentication\Token\AnonymousToken;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AnonymousListener implements ListenerInterface
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function handle(Request $request)
    {
        if (null !== $this->tokenStorage->getToken()) {
            return;
        }

        $token = $this->createAnonymousToken($request->getClientIp());
        $this->tokenStorage->setToken($token);
    }

    private function createAnonymousToken($clientIp)
    {
        $user = array(
            'id' => 0,
            'nickname' => '游客',
            'email' => 'fakeUser',
            'locale' => 'zh_CN',
            'roles' => array(),
            'currentIp' => $clientIp
        );

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));

        return new AnonymousToken($currentUser);
    }
}
