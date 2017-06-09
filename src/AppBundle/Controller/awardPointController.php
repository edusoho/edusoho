<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class awardPointController extends BaseController
{
    public function listAction(Request $request)
    {
        return $this->render('award-point/list.html.twig', array(
        ));
    }

    public function detailAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $conditions['userId'] = $user['id'];
        $accountFlowCount = $this->getAccountFlowService()->countAccountFlows($conditions);
        $paginator = new Paginator(
            $request,
            $accountFlowCount,
            10
        );

        $accountFlows = $this->getAccountFlowService()->searchAccountFlows(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('award-point/detail.html.twig', array(
            'accountFlows' => $accountFlows,
            'paginator' => $paginator,
            ));
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }
}
