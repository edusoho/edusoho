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
	    $category = $this->getCategoryService()->getCategory($categoryId);
	    return $this->render('TopxiaAdminBundle:Knowledge:list.html.twig', array(
	        'category' => $category,
	    ));
	}

	public function editAction(Request $request, $categoryId, $id)
	{
		$knowledge = $this->getKnowledgeService()->getKnowledge($id);
		if (empty($knowledge)) {
		    throw $this->createNotFoundException();
		}

		if ($request->getMethod() == 'POST') {
		    $knowledge = $this->getCategoryService()->updateCategory($id, $request->request->all());
		    return $this->createJsonResponse(true);
		}

		return $this->render('TopxiaAdminBundle:Category:modal.html.twig', array(
		    'category' => $category,
		));
	}

	public function createAction(Request $request, $categoryId)
	{
	    if ($request->getMethod() == 'POST') {
	        $knowledge = $this->getKnowledgeService()->createKnowledge($request->request->all());
	        return $this->render('TopxiaAdminBundle:Knowledge:li.html.twig', array(
	        	'knowledge' => $knowledge,
	   		));
	    }

	    $knowledge = array(
	        'id' => 0,
	        'name' => '',
	        'code' => '',
	        'description'=>'',
	        'categoryId' => (int) $categoryId,
	        'parentId' => 0,
	        'weight' => 0
	    );

	    return $this->render('TopxiaAdminBundle:Knowledge:modal.html.twig', array(
	        'knowledge' => $knowledge,
	    ));
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

	public function getKnowledgeByParentIdAction(Request $request, $categoryId, $parentId)
	{
	    $knowledges = $this->getKnowledgeService()->findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);
	    $category = $this->getCategoryService()->getCategory($categoryId);
	    return $this->render('TopxiaAdminBundle:Knowledge:ul.html.twig', array(
	        'knowledges' => $knowledges,
	        'category' => $category,
	    ));
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