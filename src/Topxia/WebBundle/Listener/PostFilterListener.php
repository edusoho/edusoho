<?php
 
namespace Topxia\WebBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Topxia\Service\Common\ServiceKernel;

class PostFilterListener
{
	public function __construct($container)
    {
        $this->container = $container;
    }

    public function onPost(GetResponseEvent $event)
    {
    	$request = $event->getRequest();
        $currentRoute = $request->attributes->get('_route');

        if(($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) && ($request->getMethod() == 'POST') && !$this->getTokenBucketService()->hasToken($request->getClientIp(), $currentRoute)){
            $response = $this->container->get('templating')->renderResponse('TopxiaWebBundle:Default:message.html.twig', array(
                'type' => 'error',
                'message' => '提交次数过多，请过会提交！',
                'goto' => '',
                'duration' => 0,
            ));

            $event->setResponse($response);
        }
    }

    protected function getTokenBucketService()
    {
        return ServiceKernel::instance()->createService('PostFilter.TokenBucketService');
    }
}