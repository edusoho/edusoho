<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class DictionaryController extends BaseController
{
	public function indexAction(Request $Request)
	{
		$dictionaryItems = $this->getDictionaryService()->findAllDictionaryItemsOrderByWeight();
        $dictionaries = $this->getDictionaryService()->findAllDictionaries();
		return $this->render('TopxiaAdminBundle:Dictionary:index.html.twig',array(
			'dictionaryItems' => $dictionaryItems,
            'dictionaries' => $dictionaries
			));
	}

	public function createAction(Request $request, $type)
    {
        if ($request->getMethod() == 'POST') {
        	$conditions = $request->request->all();
        	$conditions['type'] = $type;
        	$conditions['createdTime'] = time();
            $dictionaryItem = $this->getDictionaryService()->addDictionaryItem($conditions);
            $dictionaryItems = $this->getDictionaryService()->findAllDictionaryItemsOrderByWeight();
            $dictionaries = $this->getDictionaryService()->findAllDictionaries();
            return $this->render('TopxiaAdminBundle:Dictionary:tbody.html.twig',array(
            	'dictionaryItems' => $dictionaryItems,
                'dictionaries' => $dictionaries
            	));
        }

        return $this->render('TopxiaAdminBundle:Dictionary:modal.html.twig',array('type'=>$type));
    }

    public function checkNameAction(Request $request, $id)
    {
        $name = $request->query->get('value');
        $dictionaryItem = $this->getDictionaryService()->findDictionaryItemByName($name);

        if (empty($name)) {
            $response = array('success' => false, 'message' => '请输入名称！');
        } elseif ($dictionaryItem && $name && $dictionaryItem[0]['id'] != $id) {
            $response = array('success' => false, 'message' => '该名称已经存在！');
        } else {
            $response = array('success' => true);
        }

        return $this->createJsonResponse($response);
    }

    public function deleteAction(Request $request, $id)
    {
        $result = $this->getDictionaryService()->deleteDictionaryItem($id);
        if ($result > 0) {
            return $this->createJsonResponse(array('status' => 'ok'));
        } else {
            return $this->createJsonResponse(array('status' => 'error'));
        }
    }

    public function editAction(Request $request, $id)
    {
        $dictionaryItem = $this->getDictionaryService()->getDictionaryItem($id);
        if (empty($dictionaryItem)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $dictionaryItem = $this->getDictionaryService()->updateDictionaryItem($id, $request->request->all());
            $dictionaryItems = $this->getDictionaryService()->findAllDictionaryItemsOrderByWeight();
            $dictionaries = $this->getDictionaryService()->findAllDictionaries();
            return $this->render('TopxiaAdminBundle:Dictionary:tbody.html.twig',array(
            	'dictionaryItems' => $dictionaryItems,
                'dictionaries' => $dictionaries
            	));
        }

        return $this->render('TopxiaAdminBundle:Dictionary:modal.html.twig', array(
            'dictionaryItem' => $dictionaryItem
        ));
    }

    protected function getDictionaryService()
    {
        return $this->getServiceKernel()->createService('Dictionary.DictionaryService');
    }
}