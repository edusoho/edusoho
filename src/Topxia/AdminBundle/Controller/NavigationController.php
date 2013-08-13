<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceException;

class NavigationController extends BaseController
{
    private function getCreateForm($block = array())
    {
        $sequence = array();

        for ($i=1; $i < 11 ; $i++) { 
            $sequence[$i] = $i;
        }

        $form = $this->createFormBuilder($block)
            ->add('name', 'text')
            ->add('url', 'text')
            ->add('openNewWindow', 'choice', array(
                'expanded' => true, 
                'choices' => array('no' => '否', 'yes' => '是'),
                'data' => 'no'
            ))
            ->add('status', 'choice', array(
                'expanded' => true, 
                'choices' => array('close' => '关闭', 'open' => '开启'),
                'data' => 'close'
            ))
            ->add('type', 'choice', array(
                'expanded' => true, 
                'choices' => array('top' => '顶部导航', 'foot' => '底部导航'),
                'data' => 'top'
            ))
            ->add('sequence', 'choice', array(
                'choices' => $sequence
            ))
            ->getForm();
        return $form;
    }

    private function getEditForm($block = array())
    {
        $sequence = array();

        for ($i=1; $i < 11 ; $i++) { 
            $sequence[$i] = $i;
        }

        $form = $this->createFormBuilder($block)
            ->add('name', 'text')
            ->add('url', 'text')
            ->add('openNewWindow', 'choice', array(
                'expanded' => true, 
                'choices' => array('no' => '否', 'yes' => '是'),
            ))
            ->add('status', 'choice', array(
                'expanded' => true, 
                'choices' => array('close' => '关闭', 'open' => '开启'),
            ))
            ->add('type', 'choice', array(
                'expanded' => true, 
                'choices' => array('top' => '顶部导航', 'foot' => '底部导航'),
            ))
            ->add('sequence', 'choice', array(
                'choices' => $sequence
            ))
            ->getForm();
        return $form;
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

    public function updateAction (Request $request, $id)
    {
        $navigation = $this->getNavigationService()->getNavigation($id);
        $form = $this->getEditForm($navigation);
        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->getNavigationService()->editNavigation($id, $form->getData());
                $navigation = $this->getNavigationService()->getNavigation($id);
                $html = $this->renderView('TopxiaAdminBundle:Navigation:navigation-tr.html.twig', array('navigation'=>$navigation));
                return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
            }
        }

        return $this->render('TopxiaAdminBundle:Navigation:navigation-modal.html.twig', array(
            'form' => $form->createView(),
            'navigation'=>$navigation
        ));
    }

    public function createAction (Request $request)
    {   
        $form = $this->getCreateForm();
        if ('POST' == $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $navigationId = $this->getNavigationService()->createNavigation($form->getData());
                $navigation = $this->getNavigationService()->getNavigation($navigationId);
                $html = $this->renderView('TopxiaAdminBundle:Navigation:navigation-tr.html.twig', array('navigation'=>$navigation));
                return $this->createJsonResponse(array('status' => 'ok', 'type'=>$navigation['type'], 'html' => $html));
            }
        }

        return $this->render('TopxiaAdminBundle:Navigation:navigation-modal.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function indexAction (Request $request)
    {   
        $paginator = new Paginator(
            $request,
            $this->getNavigationService()->getNavigationsCount(),
            10
        );

        $navigations = $this->getNavigationService()->findNavigations(
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:Navigation:index.html.twig', array(
            'navigations' => $navigations,
            'paginator' => $paginator));
    }

    public function  findTopsAction(Request $request)
    {
        $paginator = new Paginator(
            $request,
            $this->getNavigationService()->getNavigationsCountByType('top'),
            10
        );

        $navigations = $this->getNavigationService()->findNavigationsByType(
            'top',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:Navigation:top-navigations.html.twig', array(
            'navigations' => $navigations,
            'paginator' => $paginator));
    }

    public function  findFootsAction(Request $request)
    {
        $paginator = new Paginator(
            $request,
            $this->getNavigationService()->getNavigationsCountByType('foot'),
            10
        );

        $navigations = $this->getNavigationService()->findNavigationsByType(
            'foot',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('TopxiaAdminBundle:Navigation:foot-navigations.html.twig', array(
            'navigations' => $navigations,
            'paginator' => $paginator));
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }

}