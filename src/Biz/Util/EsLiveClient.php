<?php

namespace Biz\Util;

use Biz\Common\CommonException;
use ESLive\SDK\ESLiveApi;
use ESLive\SDK\SDKException;
use Topxia\Service\Common\ServiceKernel;

class EsLiveClient
{
    protected $esLiveApi;

    public function __call($name, $arguments)
    {
        if (!method_exists($this->createEsLiveApi(), $name)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        try {
            return call_user_func_array([$this->createEsLiveApi(), $name], $arguments);
        } catch (SDKException $exception) {
            return [];
        }
    }

    public function createMemberGroupBundle($name)
    {
        return $this->createEsLiveApi()->createMemberGroupBundle($name);
    }

    public function batchCreateMemberGroup($bundleNo, $names)
    {
        return $this->createEsLiveApi()->batchCreateMemberGroup($bundleNo, $names);
    }

    public function batchDeleteMemberGroups($groupNos)
    {
        $this->createEsLiveApi()->batchDeleteMemberGroups($groupNos);
    }

    public function deleteMemberGroup($groupNo)
    {
        $this->createEsLiveApi()->deleteMemberGroup($groupNo);
    }

    protected function createEsLiveApi()
    {
        if (empty($this->esLiveApi)) {
            $storage = $this->getSettingService()->get('storage', []);
            $this->esLiveApi = new ESLiveApi($storage['cloud_access_key'] ?? '', $storage['cloud_secret_key'] ?? '', ['endpoint' => $storage['es_live_api_server'] ?? '']);
        }

        return $this->esLiveApi;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
