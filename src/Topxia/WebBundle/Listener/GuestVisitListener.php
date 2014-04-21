<?php
namespace Topxia\WebBundle\Listener;
 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Topxia\Service\Common\ServiceKernel;

use Topxia\Service\Common\AccessDeniedException;
 
class GuestVisitListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onGuestVisitRequest(GetResponseEvent $event)
    {
        

        $guestId = isset($_COOKIE["guestId"]) ?$_COOKIE["guestId"] : null;

        if (empty($guestId)){
               
                setcookie("guestId", time(),'/');
              
        }
        
        
    }

    protected function getCurrentUser()
    {
        return $this->getUserService()->getCurrentUser();
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
   
}