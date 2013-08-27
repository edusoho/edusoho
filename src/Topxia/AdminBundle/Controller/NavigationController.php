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

        $paginator = new Paginator(
            $request,
            $this->getNavigationService()->getNavigationsCountByType($type),
            10
        );

        $navigations = $this->getNavigationService()->findNavigationsByType(
            $type,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:Navigation:index.html.twig', array(
            'type' => $type,
            'navigations' => $navigations,
            'paginator' => $paginator));
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
            'sequence' => 0,
            'isNewWin'=>0,
            'isOpen'=>0,
            'type'=>$request->query->get('type')
        );

        return $this->render('TopxiaAdminBundle:Navigation:navigation-modal.html.twig', array(
            'navigation'=>$navigation));
    }

    public function updateAction (Request $request, $id)
    {
        $navigation = $this->getNavigationService()->getNavigation($id);
        if ('POST' == $request->getMethod()) {
                $this->getNavigationService()->updateNavigation($id, $request->request->all());
                $navigation = $this->getNavigationService()->getNavigation($id);
                return $this->renderTbody($navigation['type']);
        }

        return $this->render('TopxiaAdminBundle:Navigation:navigation-modal.html.twig', array(
            'navigation'=>$navigation
        ));
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