<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class DictionaryController extends BaseController
{
	public function indexAction(Request $Request)
	{
		$dictionaries = $this->getDictionaryService()->findAllDictionariesOrderByWeight();
		return $this->render('TopxiaAdminBundle:Dictionary:index.html.twig',array(
			'dictionaries' =>$dictionaries
			));
	}

	public function createAction(Request $request, $type)
    {
        if ($request->getMethod() == 'POST') {
        	$conditions = $request->request->all();
        	$conditions['type'] = $type;
        	$conditions['createdTime'] = time();
            $dictionary = $this->getDictionaryService()->addDictionary($conditions);
            $dictionaries = $this->getDictionaryService()->findAllDictionariesOrderByWeight();
            return $this->render('TopxiaAdminBundle:Dictionary:tbody.html.twig',array(
            	'dictionaries' =>$dictionaries
            	));
        }

        return $this->render('TopxiaAdminBundle:Dictionary:modal.html.twig',array('type'=>$type));
    }

    public function checkNameAction(Request $request, $id)
    {
        $name = $request->query->get('value');
        $dictionary = $this->getDictionaryService()->findDictionaryByName($name);

        if (empty($name)) {
            $response = array('success' => false, 'message' => '请输入名称！');
        } elseif ($dictionary && $name && $dictionary[0]['id'] != $id) {
            $response = array('success' => false, 'message' => '该名称已经存在！');
        } else {
            $response = array('success' => true);
        }

        return $this->createJsonResponse($response);
    }

    public function deleteAction(Request $request, $id)
    {
        $result = $this->getDictionaryService()->deleteDictionary($id);
        if ($result > 0) {
            return $this->createJsonResponse(array('status' => 'ok'));
        } else {
            return $this->createJsonResponse(array('status' => 'error'));
        }
    }

    public function editAction(Request $request, $id)
    {
        $dictionary = $this->getDictionaryService()->getDictionary($id);
        if (empty($dictionary)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $dictionary = $this->getDictionaryService()->updateDictionary($id, $request->request->all());
            $dictionaries = $this->getDictionaryService()->findAllDictionariesOrderByWeight();
            return $this->render('TopxiaAdminBundle:Dictionary:tbody.html.twig',array(
            	'dictionaries' =>$dictionaries
            	));
        }

        return $this->render('TopxiaAdminBundle:Dictionary:modal.html.twig', array(
            'dictionary' => $dictionary
        ));
    }

    protected function getDictionaryService()
    {
        return $this->getServiceKernel()->createService('Dictionary.DictionaryService');
    }
}