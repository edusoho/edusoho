<?php

namespace AppBundle\Controller;

use Biz\CloudPlatform\Service\AppService;
use Biz\User\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Codeages\Biz\Framework\Service\BaseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\ResourceNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class BaseController extends Controller
{
    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        return $this->getUser();
    }

    /**
     * switch current user
     *
     * @param Request       $request
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    protected function switchUser(Request $request, UserInterface $user)
    {
        $user['currentIp'] = $request->getClientIp();
        $biz = $this->getBiz();
        $biz['user'] = $user;
        $token = new UsernamePasswordToken($user, null, 'main', $user['roles']);
        $this->container->get('security.token_storage')->setToken($token);

        $this->get('event_dispatcher')->dispatch(SecurityEvents::INTERACTIVE_LOGIN, new InteractiveLoginEvent($request, $token));
        $biz->service('System:LogService')->info('user', 'login_success', '登录成功');
        return $user;
    }

    /**
     *
     * @param $pluginName
     *
     * @return bool
     */
    protected function isPluginInstalled($pluginName)
    {
        /**
         * @var $appService AppService
         */
        $appService = $this->getBiz()->service('CloudPlatform:AppService');
        $app = $appService->getAppByCode($pluginName);
        return !empty($app);
    }

    protected function getBiz()
    {
        return $this->get('biz');
    }

    public function getUser()
    {
        $biz = $this->getBiz();
        return $biz['user'];
    }

    protected function createJsonResponse($data = null, $status = 200, $headers = array())
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function createJsonpResponse($data = null, $callback = 'callback', $status = 200, $headers = array())
    {
        $response = $this->createJsonResponse($data, $status, $headers);
        return $response->setCallback($callback);
    }

    /**
     * 创建消息提示响应
     *
     * @param  string     $type     消息类型：info, warning, error
     * @param  string     $message  消息内容
     * @param  string     $title    消息抬头
     * @param  integer    $duration 消息显示持续的时间
     * @param  string     $goto     消息跳转的页面
     * @return Response
     */
    protected function createMessageResponse($type, $message, $title = '', $duration = 0, $goto = null)
    {
        if (!in_array($type, array('info', 'warning', 'error'))) {
            throw new \RuntimeException('type error');
        }

        return $this->render('default/message.html.twig', array(
            'type'     => $type,
            'message'  => $message,
            'title'    => $title,
            'duration' => $duration,
            'goto'     => $goto
        ));
    }

    protected function createResourceNotFoundException($resourceType, $resourceId, $message = '')
    {
        return new ResourceNotFoundException($resourceType, $resourceId, $message);
    }

    protected function setFlashMessage($level, $message)
    {
        $this->get('session')->getFlashBag()->add($level, $message);
    }

    protected function agentInWhiteList($userAgent)
    {
        $whiteList = array("iPhone", "iPad", "Android", "HTC");

        return ArrayToolkit::some($whiteList, function ($agent) use ($userAgent) {
            return strpos($userAgent, $agent) > -1;
        });
    }

    protected function setting($name, $default = null)
    {
        return $this->get('topxia.twig.web_extension')->getSetting($name, $default);
    }

    /**
     * @param  string        $alias
     * @return BaseService
     */
    protected function createService($alias)
    {
        $biz = $this->getBiz();
        return $biz->service($alias);
    }
}
