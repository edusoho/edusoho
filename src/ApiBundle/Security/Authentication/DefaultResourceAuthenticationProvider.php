<?php

namespace ApiBundle\Security\Authentication;

use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\ResourceProxy;
use Biz\User\UserException;
use Doctrine\Common\Annotations\CachedReader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DefaultResourceAuthenticationProvider implements ResourceAuthenticationInterface
{
    private $tokenStorage;

    private $container;

    /**
     * @var CachedReader
     */
    private $annotationReader;

    public function __construct(ContainerInterface $container)
    {
        $this->annotationReader = $container->get('annotation_reader');
        $this->tokenStorage = $container->get('security.token_storage');
        $this->container = $container;
    }

    public function authenticate(ResourceProxy $resourceProxy, $method)
    {
        $annotation = $this->annotationReader->getMethodAnnotation(
            new \ReflectionMethod(get_class($resourceProxy->getResource()), $method),
            'ApiBundle\Api\Annotation\ApiConf'
        );

        if ($annotation && !$annotation->getIsRequiredAuth()) {
            return;
        }

        $accessAnnotation = $this->annotationReader->getMethodAnnotation(
            new \ReflectionMethod(get_class($resourceProxy->getResource()), $method),
            'ApiBundle\Api\Annotation\Access'
        );

        $biz = $this->container->get('biz');
        $currentUser = $biz['user'];
        if ($accessAnnotation && !$accessAnnotation->canAccess($currentUser->getRoles())) {
            throw UserException::PERMISSION_DENIED();
        }

        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface || $token instanceof AnonymousToken) {
            throw new UnauthorizedHttpException('Basic', 'Requires authentication', null, ErrorCode::UNAUTHORIZED);
        }
    }
}
