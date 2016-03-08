<?php

namespace MaterialLib\MaterialLibBundle\Controller\Web;

use Symfony\Component\HttpFoundation\Request;
use MaterialLib\MaterialLibBundle\Controller\BaseController;
use Topxia\Common\Paginator;

class MaterialLibController extends BaseController
{
    public function reconvertAction($globalId)
    {
        $this->getMaterialLibService()->reconvert($globalId);
        return $this->createJsonResponse(array('success' => true));
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
