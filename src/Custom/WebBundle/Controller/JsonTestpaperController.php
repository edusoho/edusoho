<?php 
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class jsonTestpaperController extends BaseController
{
    public function searchAction(Request $request)
    {
        $query = $request->query->all();
        $conditions = array('title' =>'');

        if (!empty($query['mainKnowledgeId'])) {
            $conditions['mainKnowledgeId'] = $query['mainKnowledgeId'];
        }

        if (!empty($query['tagIds'])) {
            $conditions['tagIds'] = $query['tagIds'];
        }

        if (!empty($query['keyword'])) {
            $conditions['title'] = $query['keyword'];
        }

        if (!empty($query['categoryId'])) {
            $conditions['target'] = 'category-'.$query['categoryId'];
        }

        $testpapers = $this->getTestpaperService()->searchTestpapers($conditions,array('createdTime','DESC'),0,15);
        
        return $this->createJsonResponse($testpapers);
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }
}