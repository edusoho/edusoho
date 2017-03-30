<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use Pimple\Container;

interface ProcessorInterface
{
    public function setContainer(Container $container = null);

    public function execute(Request $request);
}
