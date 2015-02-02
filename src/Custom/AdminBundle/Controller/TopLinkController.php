<?php
namespace Custom\AdminBundle\Controller;

use Topxia\AdminBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class TopLinkController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getTopLinkService()->searchTopLinkCount($conditions),
            20
        );

        $topLinks = $this->getTopLinkService()->searchTopLinks(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('CustomAdminBundle:TopLink:index.html.twig',array(
            'topLinks' => $topLinks
        ));
    }

    public function createAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $topLink = $request->request->all();
            $this->getTopLinkService()->createTopLink($topLink);
            return $this->createJsonResponse(true);
        }
        return $this->render('CustomAdminBundle:TopLink:edit-modal.html.twig',array(

        ));
    }

    public function editAction(Request $request,$id)
    {
        $topLink = $this->getTopLinkService()->getTopLink($id);
        if(empty($topLink)){
            throw $this->createNotFoundException();
        }

        if($request->getMethod() == 'POST'){
            $fields = $request->request->all();
            $this->getTopLinkService()->editTopLink($id,$fields);
            return $this->createJsonResponse(true);
        }
        return $this->render('CustomAdminBundle:TopLink:edit-modal.html.twig',array(
            'topLink' => $topLink
        ));
    }

    public function removeAction(Request $request,$id)
    {
        $this->getTopLinkService()->removeTopLink($id);
        return $this->createJsonResponse(true);
    }

    private function getTopLinkService()
    {
        return $this->getServiceKernel()->createService('Custom:TopLink.TopLinkService');
    }
}