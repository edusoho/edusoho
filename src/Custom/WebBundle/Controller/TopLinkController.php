<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;


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

        return $this->render('CustomWebBundle:TopLink:index.html.twig',array(
            'topLinks' => $topLinks
        ));

    }

    private function getTopLinkService()
    {
        return $this->getServiceKernel()->createService('Custom:TopLink.TopLinkService');
    }
}