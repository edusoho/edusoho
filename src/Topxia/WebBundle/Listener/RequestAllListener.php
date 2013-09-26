<?php
namespace Topxia\WebBundle\Listener;
 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Topxia\Service\Common\ServiceKernel;

use Topxia\Service\Common\AccessDeniedException;
 
class RequestAllListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelRequestAll(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $feild['userId']=$this->getCurrentUser()->id;
        $feild['path']=$request->server->get('HTTP_REFERER');
        $feild['createTime']=time();


        //'HTTP_REFERER
        
        
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