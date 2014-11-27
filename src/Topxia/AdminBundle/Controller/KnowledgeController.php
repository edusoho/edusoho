<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class KnowledgeController extends BaseController
{
    public function listAction(Request $request, $categoryId)
    {
        $isHidden = $request->query->get('isHidden');
        if (!isset($isHidden)){
            $isHidden = true;
        }
        $category = $this->getCategoryService()->getCategory($categoryId);
        return $this->render('TopxiaAdminBundle:Knowledge:list.html.twig', array(
            'category' => $category,
            'isHidden' => $isHidden
        ));
    }

    public function editAction(Request $request)
    {
        $query = $request->query->all();
        if ($request->getMethod() == 'POST') {
            $fields =  $request->request->all();
            $id = $fields['id'];
            $fields['isHidden'] = empty($fields['isHidden']) ? 0 : $fields['isHidden'];

            $knowledge = $this->getKnowledgeService()->updateKnowledge($id, $fields);
            $result = array(
                'tid' => $fields['tid'],
                'type' => 'edit',
                'knowledge' => $knowledge,
            );
            return $this->createJsonResponse($result);
        }

        $knowledge = $this->getKnowledgeService()->getKnowledge($query['id']);
        if(empty($knowledge)) {
            $knowledge = array(
                'id' => 0,
                'name' => '',
                'code' => '',
                'description'=>'',
                'categoryId' => (int) $query['categoryId'],
                'parentId' => 0,
                'weight' => 0,
                'sequence' => 1
            );
        }
        $knowledge['tid'] = $query['tid'];
        return $this->render('TopxiaAdminBundle:Knowledge:modal.html.twig', array(
            'knowledge' => $knowledge,
        ));
    }

    public function createAction(Request $request, $categoryId)
    {
        if ($request->getMethod() == 'POST') {
            $fields =  $request->request->all();
            $knowledge = $this->getKnowledgeService()->createKnowledge($fields);
            $result = array(
                'tid' => $fields['tid'],
                'knowledge' => $knowledge
            );
            return $this->createJsonResponse($result);
        }

        $query =  $request->query->all();
        $knowledge = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'description'=>'',
            'categoryId' => (int) $categoryId,
            'weight' => 0
        );
        if(empty($query['pid'])) {
            $knowledge['parentId'] = 0;
            $knowledge['tid'] = null;
        } else {
            $knowledge['parentId'] =  $query['pid'];
            $knowledge['tid'] = $query['tid'];
        }

        if(empty($query['seq'])) {
            $knowledge['sequence'] = count($this->getKnowledgeService()->findKnowledgeByCategoryIdAndParentId($categoryId, 0)) +1;
        } else {
            $knowledge['sequence'] = $query['seq'];
        }

        return $this->render('TopxiaAdminBundle:Knowledge:modal.html.twig', array(
            'knowledge' => $knowledge,
        ));
    }

    public function deleteAction(Request $request)
    {
        $this->getKnowledgeService()->deleteKnowledge($request->request->get('id'));
        return $this->createJsonResponse(true);
    }

    public function checkCodeAction(Request $request)
    {
        $code = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getKnowledgeService()->isKnowledgeCodeAvaliable($code, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '编码已被占用，请换一个。');
        }

        return $this->createJsonResponse($response);
    }

    public function getNodesAction(Request $request)
    {
        $query = $request->request->all();
        $isHidden = $request->query->get('isHidden');
        $parentId = empty($query['id']) ? 0 : $query['id'];
        if ($isHidden){
            $knowledges = $this->getKnowledgeService()->findAllNodesData($query['categoryId'], $parentId);
        } else {
            $knowledges = $this->getKnowledgeService()->findNodesData($query['categoryId'], $parentId);
        }
        return $this->createJsonResponse($knowledges);
    }

    public function getTreeAction(Request $request,$categoryId)
    {
        return $this->render('TopxiaAdminBundle:Knowledge:tree.html.twig',array('categoryId' => $categoryId));
    }

    public function matchAction(Request $request)
    {
        $likeString = $request->query->get('q');

        $knowledges = $this->getKnowledgeService()->searchKnowledge(array('keywords'=>$likeString),array('createdTime', 'DESC'), 0, 10);
        return $this->createJsonResponse($knowledges);
    }

    public function sortAction(Request $request)
    {
        $id = $request->request->get('id');
        $parentId = $request->request->get('pid');
        $seq = $request->request->get('seq');
        $knowledge = $this->getKnowledgeService()->sort($id, empty($parentId)?0:$parentId, $seq);
        return $this->createJsonResponse($knowledge);
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }
}