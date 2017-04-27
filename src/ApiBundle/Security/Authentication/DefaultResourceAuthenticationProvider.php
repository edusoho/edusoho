<?php

namespace ApiBundle\Security\Authentication;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Exception\AccessDeniedException;
use ApiBundle\Api\Resource\ResourceProxy;
use ApiBundle\Security\Authentication\Token\ApiToken;
use Doctrine\Common\Annotations\CachedReader;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
            ApiConf::class
        );

        if ($annotation && !$annotation->getIsRequiredAuth()) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token instanceof ApiToken) {
            throw new AccessDeniedException('无权限访问资源');
        }
    }
}