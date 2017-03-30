<?php
namespace Topxia\Service\Common\Mail;

use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudMail extends Mail
{
    /**
     * @sourceFrom: 发送来源(默认不传，crm插件需传值)
     * @sendedSn: 发送批次号(默认不传，crm传入发送的批次号)
     * @return bool
     */
    public function doSend()
    {
        $cloudConfig = $this->setting('cloud_email_crm', array());

        if (isset($cloudConfig['status']) && $cloudConfig['status'] == 'enable') {
            $options = $this->options;
            $template = $this->parseTemplate($options);
            $format = isset($options['format']) && $options['format'] == 'html' ? 'html' : 'text';
            $params = array(
               'to' => $this->to,
               'title'=> $template['title'],
               'body' =>$template['body'],
               'format' => $format,
               'template'=>'email_default',
               'sourceFrom' => empty($options['sourceFrom']) ? '' : $options['sourceFrom'],
            );

            if (!empty($options['sendedSn'])) {
                $params['sendedSn'] = $options['sendedSn'];
            }
            $api    = CloudAPIFactory::create('root');
            $result = $api->post("/emails", $params);
            return empty($result['sendedSn']) ? false : true;
        }
        return false;
    }
}
