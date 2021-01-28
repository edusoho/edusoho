<?php

namespace AppBundle\Listener;

use ApiBundle\ApiBundle;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class KernelRequestListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $settingService = $this->getSettingService();

        $blacklistIps = $settingService->get('blacklist_ip');
        $whitelistIps = $settingService->get('whitelist_ip');

        $clientIp = $request->getClientIp();

        if (isset($blacklistIps['ips'])) {
            if ($this->matchIpConfigList($clientIp, $blacklistIps['ips'])) {
                throw new AccessDeniedException('您的IP已被列入黑名单，访问被拒绝，如有疑问请联系管理员！');
            }
        }

        if (isset($whitelistIps['ips'])) {
            if ($this->matchIpConfigList($clientIp, $whitelistIps['ips']) == false) {
                throw new AccessDeniedException('您的IP不在授权访问列表中，访问被拒绝，如有疑问请联系管理员！');
            }
        }

        if ($request->getMethod() === 'POST') {
            if (stripos($request->getPathInfo(), ApiBundle::API_PREFIX) === 0) {
                return;
            }

            if (stripos($request->getPathInfo(), '/mapi') === 0) {
                return;
            }

            if (stripos($request->getPathInfo(), '/hls') === 0) {
                return;
            }
            if (stripos($request->getPathInfo(), '/callback') === 0) {
                return;
            }

            $whiteList = $this->container->hasParameter('route_white_list') ? $this->container->getParameter('route_white_list') : array();

            if (in_array($request->getPathInfo(), $whiteList)) {
                return;
            }

            if ($request->isXmlHttpRequest()) {
                $token = $request->headers->get('X-CSRF-Token');
            } else {
                $token = $request->request->get('_csrf_token', '');
            }

            $request->request->remove('_csrf_token');

            $expectedToken = $this->container->get('security.csrf.token_manager')->getToken('site');
            if ($token != $expectedToken) {
                // @todo 需要区分ajax的response
                if ($request->getPathInfo() == '/admin') {
                    $token = $request->request->get('token');
                    $result = $this->getAppService()->repairProblem($token);

                    $this->container->set('Topxia.RepairProblem', $result);
                } else {
                    $response = $this->container->get('templating')->renderResponse('default/message.html.twig', array(
                        'type' => 'error',
                        'message' => $this->trans('exception.form.expire'),
                        'goto' => '',
                        'duration' => 0,
                    ));
                    $response->setStatusCode(403);
                    $event->setResponse($response);
                }
            }
        }
    }

    private function matchIpConfigList($clientIp, $ipConfigList)
    {
        foreach ($ipConfigList as $ipConfigEntry) {
            if ($this->matchIp($clientIp, $ipConfigEntry)) {
                return true;
            }
        }

        return false;
    }

    private function matchIp($clientIp, $ipConfigEntry)
    {
        $ipConfigEntry = trim($ipConfigEntry);

        if (strlen($ipConfigEntry) > 0) {
            $regex = str_replace('.', "\.", $ipConfigEntry);
            $regex = str_replace('*', "\d{1,3}", $regex);
            $regex = '/^'.$regex.'/';

            return preg_match($regex, $clientIp);
        } else {
            return false;
        }
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }

    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function trans($id, array $parameters = array())
    {
        return $this->container->get('translator')->trans($id, $parameters);
    }
}
