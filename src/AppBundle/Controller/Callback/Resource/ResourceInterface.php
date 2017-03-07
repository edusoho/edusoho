<?php

namespace AppBundle\Controller\Callback\Resource;

use Symfony\Component\HttpFoundation\Request;

interface ResourceInterface
{
    public function get(Request $request);

    public function post(Request $request);
}
