<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\AccessDeniedException;

abstract class BaseController extends Controller
{
    /**
     * 获得当前用户
     * 
     * 如果当前用户为游客，那么返回id为0, nickanme为"游客", currentIp为当前IP的CurrentUser对象。
     * 不能通过empty($this->getCurrentUser())的方式来判断用户是否登录。
     */
    protected function getCurrentUser()
    {
        return $this->getUserService()->getCurrentUser();
    }

    protected function isAdminOnline()
    {
        return $this->get('security.context')->isGranted('ROLE_ADMIN');
    }

    public function getUser()
    {
        throw new \RuntimeException('获得当前登录用户的API变更为：getCurrentUser()。');
    }

    /**
     * 创建消息提示响应
     * 
     * @param  string  $type        消息类型：info, warning, error
     * @param  string  $message     消息内容
     * @param  string  $title       消息抬头
     * @param  integer $duration    消息显示持续的时间
     * @param  string  $goto        消息跳转的页面
     * @return Response
     */
    protected function createMessageResponse($type, $message, $title = '', $duration = 0, $goto = null)
    {
        if (!in_array($type, array('info', 'warning', 'error'))) {
            throw new \RuntimeException('type不正确');
        }

        return $this->render('TopxiaWebBundle:Default:message.html.twig', array(
            'type' => $type,
            'message' => $message,
            'title' => $title,
            'duration' => $duration,
            'goto' => $goto,
        ));
    }

    protected function createMessageModalResponse($type, $message, $title = '', $duration = 0, $goto = null)
    {
        return $this->render('TopxiaWebBundle:Default:message-modal.html.twig', array(
            'type' => $type,
            'message' => $message,
            'title' => $title,
            'duration' => $duration,
            'goto' => $goto,
        ));
    }

    protected function authenticateUser ($user)
    {
        $user['currentIp'] = $this->container->get('request')->getClientIp();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        ServiceKernel::instance()->setCurrentUser($currentUser);

        $token = new UsernamePasswordToken($currentUser, null, 'main', $currentUser['roles']);
        $this->container->get('security.context')->setToken($token);

        $loginEvent = new InteractiveLoginEvent($this->getRequest(), $token);
        $this->get('event_dispatcher')->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);

        $loginBind = $this->setting('login_bind', array());
        if (empty($loginBind['login_limit'])) {
            return ;
        }

        $sessionId = $this->container->get('request')->getSession()->getId();
        $this->getUserService()->rememberLoginSessionId($user['id'], $sessionId);
    }

    protected function setFlashMessage ($level, $message)
    {
        $this->get('session')->getFlashBag()->add($level, $message);
    }

    protected function setting($name, $default = null)
    {
        return $this->get('topxia.twig.web_extension')->getSetting($name, $default);
    }

    protected function isPluginInstalled($name)
    {
        return $this->get('topxia.twig.web_extension')->isPluginInstaled($name);
    }

    protected function createNamedFormBuilder($name, $data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createNamedBuilder($name, 'form', $data, $options);
    }

    protected function sendEmail($to, $title, $body, $format = 'text')
    {
        $format == 'html' ? 'text/html' : 'text/plain';

        $config = $this->setting('mailer', array());

        if (empty($config['enabled'])) {
            return false;
        }

        $transport = \Swift_SmtpTransport::newInstance($config['host'], $config['port'])
          ->setUsername($config['username'])
          ->setPassword($config['password']);

        $mailer = \Swift_Mailer::newInstance($transport);

        $email = \Swift_Message::newInstance();
        $email->setSubject($title);
        $email->setFrom(array ($config['from'] => $config['name'] ));
        $email->setTo($to);
        if ($format == 'text/html') {
            $email->setBody($body, 'text/html');
        } else {
            $email->setBody($body);
        }

        $mailer->send($email);

        return true;
    }

    protected function createJsonResponse($data)
    {
        return new JsonResponse($data);
    }

    /**
     * JSONM
     * https://github.com/lifesinger/lifesinger.github.com/issues/118
     */
    protected function createJsonmResponse($data)
    {
        $response = new JsonResponse($data);
        $response->setCallback('define');
        return $response;
    }

    protected function createAccessDeniedException($message = null)
    {
        if ($message) {
            return new AccessDeniedException($message);
        } else {
            return new AccessDeniedException();
        }
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

}
