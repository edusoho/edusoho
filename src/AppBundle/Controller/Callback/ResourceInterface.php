<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;

interface ResourceInterface
{
    public function get(Request $request);

    public function post(Request $request);

    public function auth(Request $request);
}
