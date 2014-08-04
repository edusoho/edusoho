<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Form\RegisterType;

class RegisterController extends BaseController
{

    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            return $this->createMessageResponse('info', '你已经登录了', null, 3000, $this->generateUrl('homepage'));
        }

        $form = $this->createForm(new RegisterType());
        
        if ($request->getMethod() == 'POST') {
    
            $registration = $request->request->all();
   
            $registration['createdIp'] = $request->getClientIp();

            $user = $this->getAuthService()->register($registration);

            $this->authenticateUser($user);
            $this->sendRegisterMessage($user);

            $goto = $this->generateUrl('register_submited', array(
                'id' => $user['id'], 'hash' => $this->makeHash($user)
            ));

            if ($this->getAuthService()->hasPartnerAuth()) {
                return $this->redirect($this->generateUrl('partner_login', array('goto' => $goto)));
            }

            return $this->redirect($goto);
            
        }

        $auth=$this->getSettingService()->get('auth');

        if(!isset($auth['registerSort']))$auth['registerSort']="";
        
        $loginEnable  = $this->isLoginEnabled();

        $userFields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
        for($i=0;$i<count($userFields);$i++){
           if(strstr($userFields[$i]['fieldName'], "textField")) $userFields[$i]['type']="text";
           if(strstr($userFields[$i]['fieldName'], "varcharField")) $userFields[$i]['type']="varchar";
           if(strstr($userFields[$i]['fieldName'], "intField")) $userFields[$i]['type']="int";
           if(strstr($userFields[$i]['fieldName'], "floatField")) $userFields[$i]['type']="float";
           if(strstr($userFields[$i]['fieldName'], "dateField")) $userFields[$i]['type']="date";
        }
        
        return $this->render("TopxiaWebBundle:Register:index.html.twig", array(
            'isLoginEnabled' => $loginEnable,
            'registerSort'=>$auth['registerSort'],
            'userFields'=>$userFields,
        ));
    }

    public function userTermsAction(Request $request)
    {
        $setting = $this->getSettingService()->get('auth', array());

        return $this->render("TopxiaWebBundle:Register:user-terms.html.twig", array(
            'userTerms' => $setting['user_terms_body']
        ));
    }

    public function emailSendAction(Request $request, $id, $hash)
    {
        $user = $this->checkHash($id, $hash);
        if (empty($user)) {
            return $this->createJsonResponse(false);
        }

        $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'));
        $this->sendVerifyEmail($token, $user);

        return $this->createJsonResponse(true);
    }


    public function submitedAction(Request $request, $id, $hash)
    {
        $user = $this->checkHash($id, $hash);
        if (empty($user)) {
            throw $this->createNotFoundException();
        }

        return $this->render("TopxiaWebBundle:Register:submited.html.twig", array(
            'user' => $user,
            'hash' => $hash,
            'emailLoginUrl' => $this->getEmailLoginUrl($user['email']),
        ));
    }

    public function emailVerifyAction(Request $request, $token)
    {

        $token = $this->getUserService()->getToken('email-verify', $token);
        if (empty($token)) {
            $currentUser = $this->getCurrentUser();
            if (empty($currentUser)) {
                return $this->render('TopxiaWebBundle:Register:email-verify-error.html.twig');
            } else {
                return $this->redirect($this->generateUrl('settings'));
            }
        }

        $user = $this->getUserService()->getUser($token['userId']);
        if (empty($user)) {
            return $this->createNotFoundException();
        }

        $this->getUserService()->setEmailVerified($user['id']);

        $this->getUserService()->deleteToken('email-verify', $token['token']);

        return $this->render('TopxiaWebBundle:Register:email-verify-success.html.twig');
    }

    private function makeHash($user)
    {
        $string = $user['id'] . $user['email'] . $this->container->getParameter('secret');
        return md5($string);
    }

    private function checkHash($userId, $hash)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return false;
        }

        if ($this->makeHash($user) !== $hash) {
            return false;
        }

        return $user;
    }

    public function emailCheckAction(Request $request)
    {
        $email = $request->query->get('value');
        $email = str_replace('!', '.', $email);

        list($result, $message) = $this->getAuthService()->checkEmail($email);

        if ($result == 'success') {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $message);
        }

        return $this->createJsonResponse($response);
    }

    public function nicknameCheckAction(Request $request)
    {
        $nickname = $request->query->get('value');
        list($result, $message) = $this->getAuthService()->checkUsername($nickname);
        if ($result == 'success') {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $message);
        }
        return $this->createJsonResponse($response);
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    public function getEmailLoginUrl ($email)
    {
        $host = substr($email, strpos($email, '@') + 1);

        if ($host == 'hotmail.com') {
            return 'http://www.' . $host;
        }

        if ($host == 'gmail.com') {
            return 'http://mail.google.com';
        }

        return 'http://mail.' . $host;
    }


    public function analysisAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Register:analysis.html.twig',array());
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getMessageService()
    {
        return $this->getServiceKernel()->createService('User.MessageService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    private function  sendRegisterMessage($user)
    {
        $senderUser = array();
        $auth = $this->getSettingService()->get('auth', array());

        if (empty($auth['welcome_enabled'])) {
            return ;
        }

        if ($auth['welcome_enabled'] != 'opened') {
            return ;
        }

        if (empty($auth['welcome_sender'])) {
            return ;
        }
        
        $senderUser = $this->getUserService()->getUserByNickname($auth['welcome_sender']);
        if (empty($senderUser)) {
            return ;
        }

        $welcomeBody = $this->getWelcomeBody($user);
        if (empty($welcomeBody)) {
            return true;
        }

        if (strlen($welcomeBody) >= 1000) {
            $welcomeBody = $this->getWebExtension()->plainTextFilter($welcomeBody, 1000);
        }

        $this->getMessageService()->sendMessage($senderUser['id'], $user['id'], $welcomeBody);
        $conversation = $this->getMessageService()->getConversationByFromIdAndToId($user['id'], $senderUser['id']);
        $this->getMessageService()->deleteConversation($conversation['id']);

    }

    private function getWelcomeBody($user)
    {
        $auth = $this->getSettingService()->get('auth', array());
        $site = $this->getSettingService()->get('site', array());
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}');
        $valuesToReplace = array($user['nickname'], $site['name'], $site['url']);
        $welcomeBody = $this->setting('auth.welcome_body', '注册欢迎的内容');
        $welcomeBody = str_replace($valuesToBeReplace, $valuesToReplace, $welcomeBody);
        return $welcomeBody;
    }

    private function sendVerifyEmail($token, $user)
    {
        $auth = $this->getSettingService()->get('auth', array());
        $site = $this->getSettingService()->get('site', array());
        $emailTitle = $this->setting('auth.email_activation_title',
            '请激活你的帐号 完成注册');
        $emailBody = $this->setting('auth.email_activation_body', ' 验证邮箱内容');

        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}', '{{verifyurl}}');
        $verifyurl = $this->generateUrl('register_email_verify', array('token' => $token), true);
        $valuesToReplace = array($user['nickname'], $site['name'], $site['url'], $verifyurl);
        $emailTitle = str_replace($valuesToBeReplace, $valuesToReplace, $emailTitle);
        $emailBody = str_replace($valuesToBeReplace, $valuesToReplace, $emailBody);
        try {
            $this->sendEmail($user['email'], $emailTitle, $emailBody);
        } catch(\Exception $e) {
            $this->getLogService()->error('user', 'register', '注册激活邮件发送失败:' . $e->getMessage());
        }
    }

    private function isLoginEnabled()
    {
        $auth = $this->getSettingService()->get('auth');
        if($auth && array_key_exists('register_mode',$auth)){
           if($auth['register_mode'] == 'opened'){
               return true;
           }else{
               return false;
           }
        }
        return true;
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

}
