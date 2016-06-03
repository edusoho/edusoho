<?php
namespace Topxia\Service\Common;

use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudMail extends Mail
{
    /**
     * @return bool
     */
    public function send()
    {
        $cloudConfig = $this->setting('cloud_email', array());

        if (isset($cloudConfig['status']) && $cloudConfig['status'] == 'enable') {
            $api    = CloudAPIFactory::create('leaf');
            $site   = $this->setting('site', array());
            $params = array(
                'to'       => $this->to,
                'template' => $this->template,
                'params'   => $this->params
            );
            $result = $api->post("/emails", $params);
            return true;
        }
        return false;
    }
}
