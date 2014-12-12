<?php 
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class jsonEssayController extends BaseController
{
    public function searchAction(Request $request)
    {
        $query = $request->query->all();

        $conditions = array();

        if (!empty($query['keyword'])) {
            $conditions['title'] = $query['keyword'];
        }
        $essays = $this->getEssayService()->searchEssays($conditions,array('createdTime','DESC'),0,15);
        
        return $this->createJsonResponse($essays);
    }

    private function getEssayService()
    {
        return $this->getServiceKernel()->createService('Essay.EssayService');
    }
}