<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class TestController extends BaseController
{
    private $cloudLeafApi;

    public function searchAction(Request $request)
    {
        $this->cloudLeafApi = CloudAPIFactory::create('leaf');

        $conditions = array(
            'type' => $request->query->get('t', 'classroom'),
            'words' => $request->query->get('w')
        );
        $resp = $this->cloudLeafApi->get('/search', $conditions);
        return $this->createJsonResponse($resp);
    }
}
