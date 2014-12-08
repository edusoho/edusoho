<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AajxCategoriesController extends BaseController
{
    public function searchAction(Request $request)
    {
        $query = $request->query->all();
        $group = $this->getCategoryService()->getGroupByCode($query['group']);
        if (empty($group)) {
            throw $this->createNotFoundException();
        }
        $parentId = empty($query['id']) ? 0 : $query['id'];
        $categories = $this->getCategoryService()->findNodesData((int)$group['id'], $parentId);
        return $this->createJsonResponse($categories);
    }

    public function matchAction(Request $request)
    {
        $likeString = $request->query->get('q');

        $categories = $this->getCategoryService()->findCategoriesByLikeName($likeString);
        $categories = $this->filterCategories($categories);

        return $this->createJsonResponse($categories);
    }

    public function queryAction(Request $request)
    {
        $ids = $request->query->get('ids');
        $categories = $this->getCategoryService()->findCategoriesByIds($ids);
        return $this->createJsonResponse($categories);
    }

    public function isSubjctAction(Request $request)
    {
        $ids = $request->query->get('value');
        $ids = explode(',', $ids);
        $message = '';
        foreach ($ids as $id) {
            $category = $this->getCategoryService()->getCategory($id);
            if(empty($category)) {
                $message .= "#{id}分类不存在,";
            } else {
                if (!$category['isSubject']) {
                    $message .= $category['name'] . '不是科目,';
                } 
            }
        }
        if(empty($message)) {
            $response = array('success' => true);
        } else {
            $message = rtrim($message, ',');
            $response = array('success' => false, 'message' => $message);
        }
        return $this->createJsonResponse($response);  
    }

    private function filterCategories($categories)
    {
        $array = array();
        foreach ($categories as $key => $category) {
            $array[$key]['value'] = $category['id'];
            $array[$key]['label'] = $category['name'];
        }

        return $array;
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

}