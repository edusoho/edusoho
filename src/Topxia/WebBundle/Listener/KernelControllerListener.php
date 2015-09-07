<?php
namespace Topxia\WebBundle\Listener;
 
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\RedirectResponse;

class KernelControllerListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
    	$request = $event->getRequest();
    	
    	$whiteList = array('/site_locked');
        if (in_array($request->getPathInfo(), $whiteList)) {
            return ;
        }

    	// $currentUser = ServiceKernel::instance()->createService('User.UserService')->getCurrentUser();
    	// if($currentUser->isAdmin()){
    	// 	return;
    	// } else if($this->getWebExtension()->upgradeLocked()) {
    	// 	$event->setController(function(){
    	// 		$url = $this->container->get('router')->generate('site_locked', array());
    	// 		return new RedirectResponse($url);
    	// 	});
    	// }
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }
}