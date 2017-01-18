<?php
namespace Topxia\Service\Common\Mail;

use Topxia\Service\Common\ServiceKernel;

class TemplateToolkit
{
    private static $templates = array(
        'effect_email_reset_password' => 'getEffectEmailResetPassword',
        'email_reset_password' => 'getEmailResetPassword',
        'email_system_self_test' => 'getEmailSystemSelfTest',
        'email_registration' => 'getEmailRegistration',
        'email_reset_email' => 'getEmailResetEmail',
        'email_verify_email' => 'getEmailVerifyEmail'
    );

    public static function parseTemplate($options)
    {
        $empty = array(
            'title' => empty($options['title']) ? '' : $options['title'],
            'body' => empty($options['body']) ? '' : $options['body']
        );

        $key = $options['template'];
        if (empty($key)) {
            return $empty;
        }

        if (array_key_exists($key, self::$templates)) {
            $function = self::$templates[$key];
            return self::$function($options);
        } else {
            return $empty;
        }
    }

    protected function getEffectEmailResetPassword($options)
    {
        $arguments = array(
            '%nickname%' => $options['params']['nickname'],
            '%sitename%' => $this->getSiteName()
        );
        return array(
            'title' => $this->trans('重置您的%sitename%帐号密码', $arguments),
            'body'  => $this->renderBody('effect-reset.txt.twig', $options['params'])
        );
    }

    protected function getEmailResetPassword($options)
    {
        $arguments = array(
            '%nickname%' => $options['params']['nickname'],
            '%sitename%' => $this->getSiteName()
        );
        return array(
            'title' => $this->trans('重设%nickname%在%sitename%的密码', $arguments),
            'body'  => $this->renderBody('reset.txt.twig', $options['params'])
        );
    }

    protected static function getEmailSystemSelfTest($options)
    {
        $arguments = array(
            '%sitename%' => self::getSiteName()
        );
        return array(
            'title' => self::trans('【%sitename%】系统自检邮件', $arguments),
            'body'  => self::trans('系统邮件发送检测测试，请不要回复此邮件！')
        );
    }

    protected function getEmailRegistration($options)
    {
        $emailTitle        = $this->setting('auth.email_activation_title', $this->trans('请激活你的帐号 完成注册'));
        $emailBody         = $this->setting('auth.email_activation_body', $this->trans(' 验证邮箱内容'));
        $params = $options['params'];
        $valuesToReplace   = array($params['nickname'], $params['sitename'], $params['siteurl'], $params['verifyurl']);
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}', '{{verifyurl}}');

        $emailTitle = str_replace($valuesToBeReplace, $valuesToReplace, $emailTitle);
        $emailBody  = str_replace($valuesToBeReplace, $valuesToReplace, $emailBody);

        return array(
            'title' => $emailTitle,
            'body'  => $emailBody
        );
    }

    protected function getEmailResetEmail($options)
    {
        $arguments = array(
            '%nickname%' => $options['params']['nickname'],
            '%sitename%' => $this->getSiteName()
        );

        return array(
            'title' => $this->trans('重设%nickname%在%sitename%的电子邮箱', $arguments),
            'body'  => $this->renderBody('email-change.txt.twig', $options['params'])
        );
    }

    protected function getEmailVerifyEmail($options)
    {
        $arguments = array(
            '%nickname%' => $options['params']['nickname'],
            '%sitename%' => $this->getSiteName()
        );

        return array(
            'title' => $this->trans('验证%nickname%在%sitename%的电子邮箱', $arguments),
            'body'  => $this->renderBody('email-verify.txt.twig', $options['params'])
        );
    }

    protected static function getSiteName()
    {
        return self::setting('site.name', 'EDUSOHO');
    }

    protected static function setting($name, $default = '')
    {

        $names = explode('.', $name);

        $name = array_shift($names);

        if (empty($name)) {
            return $default;
        }

        $value = self::getSettingService()->get($name, $default);

        if (!isset($value)) {
            return $default;
        }

        if (empty($names)) {
            return $value;
        }

        $result = $value;

        foreach ($names as $name) {
            if (!isset($result[$name])) {
                return $default;
            }

            $result = $result[$name];
        }

        return $result;
    }

    private function renderBody($view, $params)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__."/template");
        $twig = new \Twig_Environment($loader);
        return  $twig->render($view, $params);
    }

    protected static function trans($message, $arguments = array())
    {
        return self::getKernel()->trans($message, $arguments);
    }

    protected static function getKernel()
    {
        return ServiceKernel::instance();
    }

    protected static function getSettingService()
    {
        return self::getKernel()->createService('System.SettingService');
    }
}
