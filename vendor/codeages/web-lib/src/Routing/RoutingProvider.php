<?php

namespace Codeages\Weblib\Routing;

interface RoutingProvider
{
    public function getEndpoint();

    public function getNamespace();

    public function getRoutes();

    public function registerHandlers();

    public function getInjections();
}
