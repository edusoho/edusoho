<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Acl\Domain;

use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Strategy for retrieving security identities.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class SecurityIdentityRetrievalStrategy implements SecurityIdentityRetrievalStrategyInterface
{
    private $roleHierarchy;
    private $authenticationTrustResolver;

    /**
     * Constructor.
     *
     * @param RoleHierarchyInterface               $roleHierarchy
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy, AuthenticationTrustResolverInterface $authenticationTrustResolver)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentities(TokenInterface $token)
    {
        $sids = array();

        // add user security identity
        if (!$token instanceof AnonymousToken) {
            try {
                $sids[] = UserSecurityIdentity::fromToken($token);
            } catch (\InvalidArgumentException $e) {
                // ignore, user has no user security identity
            }
        }

        // add all reachable roles
        if (method_exists($this->roleHierarchy, 'getReachableRoleNames')) {
            foreach ($this->roleHierarchy->getReachableRoleNames($this->getRoleNames($token)) as $role) {
                $sids[] = new RoleSecurityIdentity($role);
            }
        } else {
            // Symfony < 4.3 BC layer
            foreach ($this->roleHierarchy->getReachableRoles($token->getRoles()) as $role) {
                $sids[] = new RoleSecurityIdentity($role);
            }
        }

        // add built-in special roles
        if ($this->authenticationTrustResolver->isFullFledged($token)) {
            $sids[] = new RoleSecurityIdentity(AuthenticatedVoter::IS_AUTHENTICATED_FULLY);
            $sids[] = new RoleSecurityIdentity(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
            $sids[] = new RoleSecurityIdentity(AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY);
        } elseif ($this->authenticationTrustResolver->isRememberMe($token)) {
            $sids[] = new RoleSecurityIdentity(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
            $sids[] = new RoleSecurityIdentity(AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY);
        } elseif ($this->authenticationTrustResolver->isAnonymous($token)) {
            $sids[] = new RoleSecurityIdentity(AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY);
        }

        return $sids;
    }

    private function getRoleNames(TokenInterface $token)
    {
        if (method_exists($token, 'getRoleNames')) {
            return $token->getRoleNames();
        }

        // Symfony < 4.3 BC layer
        return array_map(function (Role $role) {
            return $role->getRole();
        }, $token->getRoles());
    }
}
