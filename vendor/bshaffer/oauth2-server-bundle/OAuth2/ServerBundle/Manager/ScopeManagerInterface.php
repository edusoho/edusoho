<?php

namespace OAuth2\ServerBundle\Manager;

use Doctrine\ORM\EntityManager;

interface ScopeManagerInterface
{
    public function __construct(EntityManager $entityManager);

    /**
     * Creates a new scope
     *
     * @param string $scope
     *
     * @param string $description
     *
     * @return Scope
     */
    public function createScope($scope, $description = null);

    /**
     * Find a single scope by the scope
     *
     * @param $scope
     * @return Scope
     */
    public function findScopeByScope($scope);

    /**
     * Find all the scopes by an array of scopes
     *
     * @param array $scopes
     * @return mixed
     */
    public function findScopesByScopes(array $scopes);
}
