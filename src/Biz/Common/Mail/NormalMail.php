<?php

namespace Biz\Common\Mail;

use Topxia\Service\Common\ServiceKernel;

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
            $transport = \Swift_SmtpTransport::newInstance($config['host'], $config['port'], $config['encryption'])
                ->setUsername($config['username'])
                ->setPassword($config['password']);

            $mailer = \Swift_Mailer::newInstance($transport);

            $email = \Swift_Message::newInstance();

            $options = $this->parseTemplate($this->options);

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
}
