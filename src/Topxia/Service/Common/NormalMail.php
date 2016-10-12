<?php
namespace Topxia\Service\Common;

class NormalMail extends Mail
{
    /**
     * @return bool
     */
    public function send()
    {
        $format = isset($this->format) && $this->format == 'html' ? 'text/html' : 'text/plain';

        $config = $this->setting('mailer', array());

        if (isset($config['enabled']) && $config['enabled'] == 1) {
            $transport = \Swift_SmtpTransport::newInstance($config['host'], $config['port'])
                ->setUsername($config['username'])
                ->setPassword($config['password']);

            $mailer = \Swift_Mailer::newInstance($transport);

            $email = \Swift_Message::newInstance();

            $options = $this->parseTemplate();

            $email->setSubject($options['title']);
            $email->setFrom(array($config['from'] => $config['name']));
            $email->setTo($this->to);

            if ($format == 'text/html') {
                $email->setBody($options['body'], 'text/html');
            } else {
                $email->setBody($options['body']);
            }

            $mailer->send($email);
            return true;
        }

        return false;
    }

    private function parseTemplate()
    {
        $empty = array(
            'title' => '',
            'body'  => ''
        );

        if (is_null($this->template)) {
            return $empty;
        }

        $method = 'on_'.$this->template;
        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method));
        } else {
            return $empty;
        }
    }

    private function on_email_reset_password()
    {
        return array(
            'title' => $this->getKernel()->trans('重设%nickname%在%sitename%的密码', array('%nickname%' => $this->params['nickname'], '%sitename%' => $this->setting('site.name', 'EDUSOHO'))),
            'body'  => $this->renderBody('TopxiaWebBundle:PasswordReset:reset.txt.twig')
        );
    }

    private function on_email_system_self_test()
    {
        return array(
            'title' => $this->getKernel()->trans('【%sitename%】系统自检邮件', array('%sitename%' => $this->params['sitename'])),
            'body'  => $this->getKernel()->trans('系统邮件发送检测测试，请不要回复此邮件！')
        );
    }

    private function on_email_registration()
    {
        $emailTitle        = $this->setting('auth.email_activation_title', $this->getKernel()->trans('请激活你的帐号 完成注册'));
        $emailBody         = $this->setting('auth.email_activation_body', $this->getKernel()->trans(' 验证邮箱内容'));
        $valuesToReplace   = array($this->params['nickname'], $this->params['sitename'], $this->params['siteurl'], $this->params['verifyurl']);
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}', '{{verifyurl}}');

        $emailTitle = str_replace($valuesToBeReplace, $valuesToReplace, $emailTitle);
        $emailBody  = str_replace($valuesToBeReplace, $valuesToReplace, $emailBody);

        return array(
            'title' => $emailTitle,
            'body'  => $emailBody
        );
    }

    private function on_email_reset_email()
    {
        return array(
            'title' => $this->getKernel()->trans('重设%nickname%在%sitename%的电子邮箱', array('%nickname%' => $this->params['nickname'], '%sitename%' => $this->setting('site.name', 'EDUSOHO'))),
            'body'  => $this->renderBody('TopxiaWebBundle:Settings:email-change.txt.twig')
        );
    }

    private function on_email_verify_email()
    {
        return array(
            'title' => $this->getKernel()->trans('验证%nickname%在%sitename%的电子邮箱', array('%nickname%' => $this->params['nickname'], '%sitename%' => $this->setting('site.name', 'EDUSOHO'))),
            'body'  => $this->renderBody('TopxiaWebBundle:Settings:email-verify.txt.twig')
        );
    }

    private function renderBody($view)
    {
        global $kernel;
        $container = $kernel->getContainer();
        return $container->get('templating')->render($view, $this->params);
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
