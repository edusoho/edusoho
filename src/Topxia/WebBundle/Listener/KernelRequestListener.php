<?php
namespace Topxia\WebBundle\Listener;
 
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Topxia\Service\Common\ServiceKernel;

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

            if (stripos($request->getPathInfo(), '/mapi') === 0) {
                return;
            }
            $whiteList = array('/coin/pay/return/alipay','/coin/pay/notify/alipay','/pay/center/pay/alipay/return', '/pay/center/pay/alipay/notify','/live/verify','/course/order/pay/alipay/notify', '/vip/pay_notify/alipay', '/uploadfile/upload', '/uploadfile/cloud_convertcallback', '/uploadfile/cloud_convertcallback2', '/uploadfile/cloud_convertcallback3', '/uploadfile/cloud_convertheadleadercallback', '/disk/upload', '/file/upload', '/editor/upload', '/disk/convert/callback', '/partner/phpwind/api/notify', '/partner/discuz/api/notify', '/live/auth');
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
                if ($request->getPathInfo() == '/admin') {
                    $token = $request->request->get('token');
                    $result = ServiceKernel::instance()->createService('CloudPlatform.AppService')->repairProblem($token);

                    $this->container->set('Topxia.RepairProblem', $result);
                } else {
        			$response = $this->container->get('templating')->renderResponse('TopxiaWebBundle:Default:message.html.twig', array(
        				'type' => 'error',
        				'message' => '页面已过期，请重新提交数据！',
        				'goto' => '',
        				'duration' => 0,
    				));

        			$event->setResponse($response);
                }

    		}
    	}
    }

}