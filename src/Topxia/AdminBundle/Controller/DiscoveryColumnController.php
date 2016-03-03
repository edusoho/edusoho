<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Service\Util\CloudClientFactory;

class DiscoveryColumnController extends BaseController
{
	public function mobileDeleteAction(Request $request, $id)
    {
        $this->getDiscoveryColumnService()->deleteDiscoveryColumn($id);
        return $this->redirect($this->generateUrl('admin_operation_mobile_class'));
    }

    public function mobileClassAction(Request $request)
    {
        $discoveryColumns = array();
        $discoveryColumns = $this->getDiscoveryColumnService()->getAllDiscoveryColumns();

        return $this->render('TopxiaAdminBundle:System:mobile-class-category.html.twig',array('discoveryColumns' => $discoveryColumns));
    }

    public function createAction(Request $request)
    {
        $categoryId = array();
        if ($request->getMethod() == 'POST') {
            $conditions = $request->request->all();
            $conditions['createdTime'] = time();
            
            $discoveryColumn = $this->getDiscoveryColumnService()->findDiscoveryColumnByTitle($conditions['title']);
            if (empty($discoveryColumn)) {
                $discoveryColumn = $this->getDiscoveryColumnService()->addDiscoveryColumn($conditions);
            }
            return $this->redirect($this->generateUrl('admin_operation_mobile_class'));
        }
        if (empty($categoryId)) {
            $categoryId = 0;
        }

        if (empty($discoveryColumn)) {
            $discoveryColumn = array();
        }
        return $this->render('TopxiaAdminBundle:System:mobile-category-modal.html.twig',array(
                'discoveryColumn' => $discoveryColumn,
                'categoryId' => $categoryId
            ));
    }

    public function mobileEditAction(Request $request, $id)
    {
        $discoveryColumn = $this->getDiscoveryColumnService()->getDiscoveryColumn($id);
        if (empty($discoveryColumn)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $conditions = $request->request->all();
            $discoveryColumn = $this->getDiscoveryColumnService()->updateDiscoveryColumn($id, $conditions);
            return $this->redirect($this->generateUrl('admin_operation_mobile_class'));
        }

        return $this->render('TopxiaAdminBundle:System:mobile-category-modal.html.twig', array(
            'discoveryColumn' => $discoveryColumn,
            'categoryId' => $discoveryColumn['categoryId']
        ));
    }

    protected function getDiscoveryColumnService()
    {
        return $this->getServiceKernel()->createService('DiscoveryColumn.DiscoveryColumnService');
    }
}