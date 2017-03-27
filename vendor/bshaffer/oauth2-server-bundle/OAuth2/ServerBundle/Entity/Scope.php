<?php

namespace OAuth2\ServerBundle\Entity;

/**
 * Powma\ServiceBundle\Entity\User
 */
class Scope
{
    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $description;

    /**
     * Set scope
     *
     * @param  string $scope
     * @return Scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set description
     *
     * @param  string $description
     * @return Scope
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
