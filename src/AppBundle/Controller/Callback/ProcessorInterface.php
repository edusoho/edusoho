<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;

interface ProcessorInterface
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function execute(Request $request);
}
