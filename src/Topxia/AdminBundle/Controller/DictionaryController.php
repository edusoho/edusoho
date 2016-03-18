<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class DictionaryController extends BaseController
{
	public function indexAction(Request $Request)
	{
		$dictionaryItems = $this->getDictionaryItemService()->findAllDictionaryItemsOrderByWeight();
		return $this->render('TopxiaAdminBundle:Dictionary:index.html.twig',array(
			'dictionaryItems' =>$dictionaryItems
			));
	}

	public function createAction(Request $request, $type)
    {
        if ($request->getMethod() == 'POST') {
        	$conditions = $request->request->all();
        	$conditions['type'] = $type;
        	$conditions['createdTime'] = time();
            $dictionaryItem = $this->getDictionaryItemService()->addDictionaryItem($conditions);
            $dictionaryItems = $this->getDictionaryItemService()->findAllDictionaryItemsOrderByWeight();
            return $this->render('TopxiaAdminBundle:Dictionary:tbody.html.twig',array(
            	'dictionaryItems' =>$dictionaryItems
            	));
        }

        return $this->render('TopxiaAdminBundle:Dictionary:modal.html.twig',array('type'=>$type));
    }

    public function checkNameAction(Request $request, $id)
    {
        $name = $request->query->get('value');
        $dictionaryItem = $this->getDictionaryItemService()->findDictionaryItemByName($name);

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
        $result = $this->getDictionaryItemService()->deleteDictionaryItem($id);
        if ($result > 0) {
            return $this->createJsonResponse(array('status' => 'ok'));
        } else {
            return $this->createJsonResponse(array('status' => 'error'));
        }
    }

    public function editAction(Request $request, $id)
    {
        $dictionaryItem = $this->getDictionaryItemService()->getDictionaryItem($id);
        if (empty($dictionaryItem)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $dictionaryItem = $this->getDictionaryItemService()->updateDictionaryItem($id, $request->request->all());
            $dictionaryItems = $this->getDictionaryItemService()->findAllDictionaryItemsOrderByWeight();
            return $this->render('TopxiaAdminBundle:Dictionary:tbody.html.twig',array(
            	'dictionaryItems' =>$dictionaryItems
            	));
        }

        return $this->render('TopxiaAdminBundle:Dictionary:modal.html.twig', array(
            'dictionaryItem' => $dictionaryItem
        ));
    }

    protected function getDictionaryItemService()
    {
        return $this->getServiceKernel()->createService('Dictionary.DictionaryItemService');
    }
}