<?php
namespace Topxia\WebBundle\Listener;
 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Topxia\Service\Common\ServiceKernel;

use Topxia\Service\Common\AccessDeniedException;
 
class LinkSaleTookenListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onLinkSaleTookenRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $mtookeen = $request->query->get('mu');

        $mTookeenCookie = isset($_COOKIE["mu"]) ?$_COOKIE["mu"] : null;

        if (empty($mTookeenCookie)){           

            if(!empty($mtookeen)){

                $linksale = $this->getLinkSaleService()->getLinkSaleBymTookeen($mtookeen);

                if(!empty( $linksale) ){
                        
                    setcookie("mu",  $mtookeen, time()+3600*24*$linksale['adCommissionDay'],'/');

                }
            }
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

    protected function getLinkSaleService()
    {
        return $this->getServiceKernel()->createService('Sale.LinkSaleService');
    }
}