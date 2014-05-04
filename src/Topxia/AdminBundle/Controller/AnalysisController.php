<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;


class AnalysisController extends BaseController
{

    public function userStateAction(Request $request)
    {

        $conditions = $request->query->all();

        $count = $this->getUserStateService()->searchUserStateCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $userStates = $this->getUserStateService()->searchUserStates($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        return $this->render('TopxiaAdminBundle:Analysis:user-state.html.twig', array(
            'conditions' => $conditions,
            'userStates' => $userStates ,  
            'paginator' => $paginator
        ));        
    }

    public function guestStateAction(Request $request)
    {

        $conditions = $request->query->all();

        $count = $this->getGuestStateService()->searchGuestStateCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $guestStates = $this->getGuestStateService()->searchGuestStates($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        return $this->render('TopxiaAdminBundle:Analysis:guest-state.html.twig', array(
            'conditions' => $conditions,
            'guestStates' => $guestStates ,  
            'paginator' => $paginator
        ));        
    }

   

    protected function getUserStateService()
    {
        return $this->getServiceKernel()->createService('State.UserStateService');
    }

    protected function getGuestStateService()
    {
        return $this->getServiceKernel()->createService('State.GuestStateService');
    }

   
}
