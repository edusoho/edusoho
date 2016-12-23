<?php

namespace AppBundle\Controller;

use Biz\CloudPlatform\Service\AppService;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Service\BaseService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\ResourceNotFoundException;


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
     * 判断是否微信内置浏览器访问
     *
     * @return bool
     */
    protected function isWxClient()
    {
        return $this->isMobileClient() && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false;
    }

    /**
     * 是否移动端访问访问
     *
     * @return bool
     */
    protected function isMobileClient()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }

        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }

        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp',
                'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
                'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi',
                'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
            );

            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(".implode('|', $clientkeywords).")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }

        //协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }

        return false;
    }

    /**
     * switch current user
     *
     * @param Request     $request
     * @param CurrentUser $user
     *
     * @return CurrentUser
     */
    protected function switchUser(Request $request, CurrentUser $user)
    {
        $user['currentIp'] = $request->getClientIp();
        $biz               = $this->getBiz();
        $biz['user']       = $user;
        $token             = new UsernamePasswordToken($user, null, 'main', $user['roles']);
        $this->container->get('security.token_storage')->setToken($token);

        $this->get('event_dispatcher')->dispatch(SecurityEvents::INTERACTIVE_LOGIN, new InteractiveLoginEvent($request, $token));
        $biz->service('System:LogService')->info('user', 'login_success', '登录成功');
        return $user;
    }

    protected function authenticateUser($user)
    {
        $user['currentIp'] = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
        $currentUser       = new CurrentUser();
        $currentUser->fromArray($user);
        return $this->switchUser($this->get('request_stack')->getCurrentRequest(), $currentUser);
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
        $app        = $appService->getAppByCode($pluginName);
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

    protected function getTargetPath(Request $request)
    {
        if ($request->query->get('goto')) {
            $targetPath = $request->query->get('goto');
        } elseif ($request->getSession()->has('_target_path')) {
            $targetPath = $request->getSession()->get('_target_path');
        } else {
            $targetPath = $request->headers->get('Referer');
        }

        if ($targetPath == $this->generateUrl('login', array(), true)) {
            return $this->generateUrl('homepage');
        }

        $url = explode('?', $targetPath);

        if ($url[0] == $this->generateUrl('partner_logout', array(), true)) {
            return $this->generateUrl('homepage');
        }

        if ($url[0] == $this->generateUrl('password_reset_update', array(), true)) {
            $targetPath = $this->generateUrl('homepage', array(), true);
        }

        if (strpos($url[0], $request->getPathInfo()) > -1) {
            $targetPath = $this->generateUrl('homepage', array(), true);
        }

        if (strpos($url[0], 'callback') !== false
            || strpos($url[0], '/login/bind') !== false
            || strpos($url[0], 'crontab') !== false
        ) {
            $targetPath = $this->generateUrl('homepage', array(), true);
        }

        if (empty($targetPath)) {
            $targetPath = $this->generateUrl('homepage', array(), true);
        }

        return $targetPath;
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
     * @param  string  $type     消息类型：info, warning, error
     * @param  string  $message  消息内容
     * @param  string  $title    消息抬头
     * @param  integer $duration 消息显示持续的时间
     * @param  string  $goto     消息跳转的页面
     *
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
     * @param  string $alias
     *
     * @return BaseService
     */
    protected function createService($alias)
    {
        $biz = $this->getBiz();
        return $biz->service($alias);
    }
}
