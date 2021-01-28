<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\Service\PushService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;

class PushServiceImpl extends BaseService implements PushService
{
    protected $imApi;

    public function __construct(Biz $biz)
    {
        parent::__construct($biz);
        $this->imApi = IMAPIFactory::create();
    }

    public function push($from, $to, $body)
    {
        $setting = $this->getSettingService()->get('app_im', array());
        if (empty($setting['enabled'])) {
            return;
        }

        $params = array(
            'fromId' => 0,
            'fromName' => '系统消息',
            'toName' => '全部',
            'body' => array(
                'v' => 1,
                't' => 'push',
                'b' => $body,
                's' => $from,
                'd' => $to,
            ),
            'convNo' => empty($to['convNo']) ? '' : $to['convNo'],
        );

        if ('user' == $to['type']) {
            $params['toId'] = $to['id'];
        }

        if (empty($params['convNo'])) {
            return;
        }
        $biz = $this->biz;
        $type = empty($body['type']) ? 'DEFAULT' : $body['type'];
        $biz['logger']->info("MESSAGE PUSH: {$type}", $params);

        try {
            $api = $this->imApi;
            $result = $api->post('/push', $params);

            $setting = $this->getSettingService()->get('developer', array());
            if (!empty($setting['debug'])) {
                IMAPIFactory::getLogger()->debug('API RESULT', !is_array($result) ? array() : $result);
            }
        } catch (\Exception $e) {
            IMAPIFactory::getLogger()->warning('API REQUEST ERROR:'.$e->getMessage());
        }
    }

    /**
     * @param $api
     * 仅供单元测试使用，正常业务禁止使用
     */
    public function setImApi($api)
    {
        $this->imApi = $api;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
