<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

class FileController extends BaseController
{
    public function indexAction(Request $request)
    {
        $form = $this->buildFileSearchForm();
        $form->handleRequest($request);
        $conditions = $form->getData();
        $group = empty($conditions['group']) ? null : $conditions['group'];
        $files = $this->getFileService()->getFiles($group, 0, 30);

        return $this->render('admin/file/index.html.twig', array(
            'files' => $files,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getFileService()->deleteFile($id);

        return $this->createNewJsonResponse(true);
    }

    protected function buildFileSearchForm()
    {
        $groups = $this->getFileService()->getAllFileGroups();

        $groupChoices = array();
        foreach ($groups as $group) {
            $groupChoices[$group['code']] = $group['name'];
        }

        return $this->createFormBuilder()
            ->add('group', ChoiceType::class, array(
                'choices' => $groupChoices,
                'empty_value' => '--文件组--',
                'required' => false,
            ))
            ->getForm();
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
