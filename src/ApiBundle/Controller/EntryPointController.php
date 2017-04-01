<?php

namespace ApiBundle\Controller;

use ApiBundle\Api\Util\RequestUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $this->initEnv();

        $biz = $this->container->get('biz');
        $kernel = $biz['api.resource.kernel'];
        return new JsonResponse($kernel->handle($request));
    }

    private function initEnv()
    {
        RequestUtil::setRequest($this->container->get('request'));
    }
}
