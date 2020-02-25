<?php

namespace Biz\Live\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use AppBundle\Common\ArrayToolkit;
use Biz\Util\EdusohoLiveClient;
use Biz\Activity\Service\LiveActivityService;

class LiveStatisticsJob extends AbstractJob
{
    private $liveApi;

    public function execute()
    {
        $this->getLiveStatisticsService()->createLiveCheckinStatistics($this->args['liveId']);
    }

    private function getLiveStatisticsService()
    {
        return $this->biz->service('Live:LiveStatisticsService');
    }
}