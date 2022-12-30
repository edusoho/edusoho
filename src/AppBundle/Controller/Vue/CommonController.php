<?php

namespace AppBundle\Controller\Vue;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExtensionManager;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class CommonController extends BaseController
{
    public function getCategoryChoicesAction(Request $request, $type = 'course')
    {
        $group = $this->getCategoryService()->getGroupByCode($type);
        $categoryTree = $this->getCategoryService()->getCategoryStructureTree($group['id']);

        return $this->createJsonResponse($categoryTree);
    }

    public function renderOrgAction(Request $request)
    {
        return $this->render('org/org-tree-select-webpack.html.twig', [
            'colmd' => $request->query->get('colMd'),
            'labelcolmd' => $request->query->get('labelColMd'),
            'nocolmd' => $request->query->get('noCloMd'),
            'inputClass' => $request->query->get('inputClass'),
            'mode' => $request->query->get('mode'),
            'orgCode' => $request->query->get('orgCode'),
            'withoutFormGroup' => $request->query->get('withoutFormGroup'),
        ]);
    }

    public function uploadImageAction(Request $request)
    {
        return $this->render('common/upload-image.html.twig', [
            'saveUrl' => $request->query->get('saveUrl'),
            'targetImg' => $request->query->get('targetImg', ''),
            'cropWidth' => $request->query->get('cropWidth', '480'),
            'cropHeight' => $request->query->get('cropHeight', '270'),
            'uploadToken' => $request->query->get('uploadToken', 'tmp'),
            'imageClass' => $request->query->get('imageClass', ''),
            'imageText' => $request->query->get('imageText', ''),
            'imageSrc' => $request->query->get('imageSrc', ''),
        ]);
    }

    public function getDataTagAction(Request $request, $name)
    {
        $arguments = $request->query->all();

        $datatag = ExtensionManager::instance()->getDataTag($name);

        return $this->createJsonResponse($datatag->getData($arguments));
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
