<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class JsonKnowledgesController extends BaseController
{
    public function searchAction(Request $request)
    {
        $query = $request->query->all();

        $parentId = empty($query['id']) ? 0 : $query['id'];
        $categoryId = empty($query['categoryId']) ? 0 : $query['categoryId'];
        $knowledges = $this->getKnowledgeService()->findAllNodesData($categoryId, $parentId);

        return $this->createJsonResponse($knowledges);
    }

    public function matchAction(Request $request)
    {
        $likeString = $request->query->get('q');

        $knowledges = $this->getKnowledgeService()->searchKnowledge(array('keywords'=>$likeString),array('createdTime', 'DESC'), 0, 10);
        $knowledges = $this->filterKnowledges($knowledges);

        return $this->createJsonResponse($knowledges);
    }

    public function queryAction(Request $request)
    {
        $ids = $request->query->get('ids');
        // $ids = explode(',', $ids[0]);
        $knowledges = $this->getKnowledgeService()->findKnowledgeByIds($ids);
        return $this->createJsonResponse($knowledges);
    }

    private function filterKnowledges($knowledges)
    {
        $array = array();
        foreach ($knowledges as $key => $knowledge) {
            $array[$key]['value'] = $knowledge['id'];
            $array[$key]['label'] = $knowledge['name'];
        }

        return $array;
    }

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }
}