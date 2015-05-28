<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceException;

class NavigationController extends BaseController
{

    public function indexAction(Request $request)
    {
        $type = $request->query->get('type', 'top');

        $navigations = $this->getNavigationService()->getNavigationsListByType($type);

        return $this->render('TopxiaAdminBundle:Navigation:index.html.twig', array(
            'type' => $type,
            'navigations' => $navigations,
        ));
    }

    public function deleteAction (Request $request, $id)
    {
        $result = $this->getNavigationService()->deleteNavigation($id);
        if($result > 0){
            return $this->createJsonResponse(array('status' => 'ok'));
        } else {
            return $this->createJsonResponse(array('status' => 'error'));
        }
    }

    public function createAction (Request $request)
    {   

        if ('POST' == $request->getMethod()) {
            $navigation = $request->request->all();
            $navigationId = $this->getNavigationService()->createNavigation($navigation);
            $navigation = $this->getNavigationService()->getNavigation($navigationId);
            return $this->renderTbody($navigation['type']);
        }

        $navigation = array(
            'id' => 0,
            'name' => '',
            'url' => '',
            'isNewWin'=>0,
            'isOpen'=>1,
            'type'=>$request->query->get('type'),
            'parentId' => $request->query->get('parentId', 0),
        );
        $parentNavigation = $navigation['parentId'] ? $this->getNavigationService()->getNavigation($navigation['parentId']) : null;

        return $this->render('TopxiaAdminBundle:Navigation:navigation-modal.html.twig', array(
            'navigation'=>$navigation,
            'parentNavigation' => $parentNavigation
        ));
    }

    public function updateAction (Request $request, $id)
    {
        $navigation = $this->getNavigationService()->getNavigation($id);
        $parentNavigation = $navigation['parentId'] ? $this->getNavigationService()->getNavigation($navigation['parentId']) : null;
        if ('POST' == $request->getMethod()) {
                $this->getNavigationService()->updateNavigation($id, $request->request->all());
                $navigation = $this->getNavigationService()->getNavigation($id);
                return $this->renderTbody($navigation['type']);
        }

        return $this->render('TopxiaAdminBundle:Navigation:navigation-modal.html.twig', array(
            'navigation'=>$navigation,
            'parentNavigation' => $parentNavigation
        ));
    }

    public function updateSeqsAction(Request $request)
    {
        $data = $request->request->get('data');
        $ids = ArrayToolkit::column($data, 'id');
        $this->getNavigationService()->updateNavigationsSequenceByIds($ids);
        return $this->createJsonResponse(true);
    }

    private function renderTbody($type)
    {
        $footNavigations = $this->getNavigationService()->findNavigationsByType($type, 0 ,20);
        return $this->render('TopxiaAdminBundle:Navigation:tbody.html.twig', array(
            'navigations'=>$footNavigations
        ));
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }

}