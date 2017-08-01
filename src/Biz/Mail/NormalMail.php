<?php

namespace Biz\Mail;

class NormalMail extends Mail
{
    /**
     * @return bool
     */
    public function doSend()
    {
        $format = isset($this->format) && $this->format == 'html' ? 'text/html' : 'text/plain';

        $config = $this->setting('mailer', array());

        if (isset($config['enabled']) && $config['enabled'] == 1) {
            $transport = \Swift_SmtpTransport::newInstance($config['host'], $config['port'])
                ->setUsername($config['username'])
                ->setPassword($config['password']);

            $mailer = \Swift_Mailer::newInstance($transport);

            $email = \Swift_Message::newInstance();

            $template = $this->parseTemplate($this->options['template']);
            $email->setSubject($template['title']);

            $email->setFrom(array($config['from'] => $config['name']));

            $email->setTo($this->to);

            if ($format == 'text/html') {
                $email->setBody($template['body'], 'text/html');
            } else {
                $email->setBody($template['body']);
            }

            $mailer->send($email);

            return true;
        }

        return false;
    }
}
