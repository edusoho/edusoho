<?php
namespace Topxia\WebBundle\Listener;
 
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class KernelRequestListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
    	$request = $event->getRequest();
    	if (($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) && ($request->getMethod() == 'POST')) {
            $whiteList = array('/course/order/pay/alipay/notify', '/uploadfile/upload', '/uploadfile/cloud_convertcallback', '/disk/upload', '/file/upload', '/kindeditor/upload', '/disk/convert/callback', '/partner/windid');
            if (in_array($request->getPathInfo(), $whiteList)) {
                return ;
            }

    		if ($request->isXmlHttpRequest()) {
    			$token = $request->headers->get('X-CSRF-Token');
    		} else {
	    		$token = $request->request->get('_csrf_token', '');
            }
    		$request->request->remove('_csrf_token');

    		$expectedToken = $this->container->get('form.csrf_provider')->generateCsrfToken('site');

    		if ($token != $expectedToken) {

                // @todo 需要区分ajax的response

    			$response = $this->container->get('templating')->renderResponse('TopxiaWebBundle:Default:message.html.twig', array(
    				'type' => 'error',
    				'message' => '数据来路不明，请回到原始页面，刷新后重试！',
    				'goto' => '',
    				'duration' => 0,
				));

    			$event->setResponse($response);
    		}
    	}
    }

}