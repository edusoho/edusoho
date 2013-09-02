<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Form\FileUploadType;

class FileController extends BaseController
{
    public function indexAction(Request $request)
    {
    	$form = $this->buildFileSearchForm();
    	$form->bind($request);
    	$conditions = $form->getData();
    	$group = empty($conditions['group']) ? null : $conditions['group'];
    	$files = $this->getFileService()->getFiles($group, 0, 30);
        return $this->render('TopxiaAdminBundle:File:index.html.twig', array(
        	'files' => $files,
        	'form' => $form->createView()
        ));
    }

    public function deleteAction(Request $request, $id)
    {
    	$this->getFileService()->deleteFile($id);
    	return $this->createNewJsonResponse(true);
    }

    private function buildFileSearchForm()
    {
    	$groups = $this->getFileService()->getAllFileGroups();

    	$groupChoices = array();
    	foreach ($groups as $group) {
    		$groupChoices[$group['code']] = $group['name'];
    	}

    	return $this->createFormBuilder()
    		->add('group', 'choice', array(
    			'choices' => $groupChoices,
    			'empty_value' => '--文件组--',
    			'required' => false
			))
			->getForm();
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }
}