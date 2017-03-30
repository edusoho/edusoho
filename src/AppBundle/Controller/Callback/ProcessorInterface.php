<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface ProcessorInterface
{
    public function setContainer(ContainerInterface $container = null);

    public function execute(Request $request);
}
