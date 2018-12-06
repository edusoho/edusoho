<?php

namespace Biz\Mail;

use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Common\CommonException;

class CloudMail extends Mail
{
    /**
     * @sourceFrom: 发送来源(默认不传，crm插件需传值)
     * @sendedSn: 发送批次号(默认不传，crm传入发送的批次号)
     *
     * @return bool
     */
    public function doSend()
    {
        $cloudConfig = $this->setting('cloud_email_crm', array());

        if (isset($cloudConfig['status']) && $cloudConfig['status'] == 'enable') {
            $options = $this->options;
            $template = $this->parseTemplate($options['template']);
            $format = isset($options['format']) && $options['format'] == 'html' ? 'html' : 'text';
            $this->checkType($options);
            $params = array(
                'to' => $this->to,
                'title' => $template['title'],
                'body' => $template['body'],
                'format' => $format,
                'template' => 'email_default',
                'sourceFrom' => empty($options['sourceFrom']) ? '' : $options['sourceFrom'],
                'type' => empty($options['type']) ? 'transaction' : $options['type'],
            );

            if (!empty($options['sendedSn'])) {
                $params['sendedSn'] = $options['sendedSn'];
            }
            $api = CloudAPIFactory::create('root');
            $result = $api->post('/emails', $params);

            return empty($result['sendedSn']) ? false : true;
        }

        return false;
    }

    private function checkType($options)
    {
        $allowedTypes = array(
            'market',
            'transaction',
        );
        if (empty($options['type']) || in_array($options['type'], $allowedTypes)) {
            return;
        }

        throw CommonException::ERROR_PARAMETER();
    }
}
