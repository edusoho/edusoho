<?php

namespace AppBundle\Extension;

use Deployer\Exception\Exception;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class FavoriteTypesExtension extends Exception implements ServiceProviderInterface
{
    public function getFavoriteTypes()
    {
        return [];
    }

    public function register(Container $container)
    {
        // TODO: Implement register() method.
    }
}
