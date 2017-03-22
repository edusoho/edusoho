<?php

namespace ApiBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class TokenAuthenticator implements SimplePreAuthenticatorInterface
{
    private $container;

    private $whiteList = array(
        'GET'  => array(
            '/^\/api\/users\/\d+$/',
            '/^\/api\/course_sets\/\d+$/',
            '/^\/api\/mobileschools\/.+$/',
            '/^\/api\/classrooms\/\w+\/members$/',
            '/^\/api\/discovery_columns$/',
            '/^\/api\/courses\/discovery\/columns$/',
            '/^\/api\/classrooms\/discovery\/columns$/',
            '/^\/api\/lessons$/',
            '/^\/api\/lessons\/\d+$/',
            '/^\/api\/classroom_play\/\d+$/',
            '/^\/api\/course\/\d+\/lessons$/',
            '/^\/api\/setting\/\w+$/',
            '/^\/api\/courses\/\w+\/members$/'
        ),
        'POST' => array(
            '/^\/api\/users$/',
            '/^\/api\/users\/login$/',
            '/^\/api\/users\/bind_login$/',
            '/^\/api\/sms_codes$/',
            '/^\/api\/users\/password$/',
            '/^\/api\/emails$/'
        )
    );

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function createToken(Request $request, $providerKey)
    {
        $auth = new ApiAuth($this->whiteList);

        $apiToken = $auth->auth($request);

        return new PreAuthenticatedToken(
            'anon.',
            $apiToken,
            $providerKey
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof TokenUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of TokenUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $apiToken = $token->getCredentials();

        $user = $userProvider->loadUserByUsername($apiToken);

        return new PreAuthenticatedToken(
            $user,
            $apiToken,
            $providerKey,
            $user->getRoles()
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response(
        // this contains information about *why* authentication failed
        // use it, or return your own message
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            401
        );
    }
}