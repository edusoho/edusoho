<?php

namespace Biz\CloudPlatform\Job;

use Biz\EduCloud\Service\ConsultService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloudConsultFreshJob extends AbstractJob
{
    public function execute()
    {
        $cloudConsult = $this->getSettingService()->get('cloud_consult', array());
        if (empty($cloudConsult)) {
            return;
        }
        $account = $this->getConsultService()->getAccount();
        $cloudConsult['cloud_consult_code'] = empty($account['code']) ? 0 : $account['code'];
        $this->getSettingService()->set('cloud_consult', $cloudConsult);
    }

    /**
     * @return ConsultService
     */
    protected function getConsultService()
    {
        return $this->biz->service('EduCloud:MicroyanConsultService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
