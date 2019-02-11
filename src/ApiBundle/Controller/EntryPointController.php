<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class EntryPointController extends Controller
{
    /**
     * @Route("/{res1}")
     * @Route("/{res1}/{slug1}")
     * @Route("/{res1}/{slug1}/{res2}")
     * @Route("/{res1}/{slug1}/{res2}/{slug2}")
     * @Route("/{res1}/{slug1}/{res2}/{slug2}/{res3}")
     * @Route("/{res1}/{slug1}/{res2}/{slug2}/{res3}/{slug3}")
     * @Route("/{res1}/{slug1}/{res2}/{slug2}/{res3}/{slug3}/{res4}")
     * @Route("/{res1}/{slug1}/{res2}/{slug2}/{res3}/{slug3}/{res4}/{slug4}")
     */
    public function startAction(Request $request)
    {
        $kernel = $this->container->get('api_resource_kernel');

        return $this->get('api_response_viewer')->view($kernel->handle($request));
    }
}
