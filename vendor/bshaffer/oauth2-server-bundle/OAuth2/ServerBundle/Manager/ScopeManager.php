<?php

namespace OAuth2\ServerBundle\Manager;

use Doctrine\ORM\EntityManager;

class ScopeManager implements ScopeManagerInterface
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Creates a new scope
     *
     * @param string $scope
     *
     * @param string $description
     *
     * @return Scope
     */
    public function createScope($scope, $description = null)
    {
        if ($scopeObject = $this->findScopeByScope($scope)) {
          return $scopeObject;
        }

        $scopeObject = new \OAuth2\ServerBundle\Entity\Scope();
        $scopeObject->setScope($scope);
        $scopeObject->setDescription($description);

        // Store Scope
        $this->em->persist($scopeObject);
        $this->em->flush();

        return $scopeObject;
    }

    /**
     * Find a single scope by the scope
     *
     * @param $scope
     * @return Scope
     */
    public function findScopeByScope($scope)
    {
        $scopeObject = $this->em->getRepository('OAuth2ServerBundle:Scope')->find($scope);

        return $scopeObject;
    }

    /**
     * Find all the scopes by an array of scopes
     *
     * @param array $scopes
     * @return mixed|void
     */
    public function findScopesByScopes(array $scopes)
    {
        $scopeObjects = $this->em->getRepository('OAuth2ServerBundle:Scope')
            ->createQueryBuilder('a')
            ->where('a.scope in (?1)')
            ->setParameter(1, $scopes)
            ->getQuery()->getResult();

        return $scopeObjects;
    }
}
