<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class MultiClassClone extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $existed = $this->getMultiClassService()->getMultiClass($id);

        if (!$existed) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $jobName = 'CloneMultiClassJob_'.$id;
        $jobs = $this->getSchedulerService()->countJobs(['name' => $jobName, 'deleted' => 0]);

        if ($jobs) {
            throw MultiClassException::MULTI_CLASS_CLONE_ALREADY();
        } else {
            $this->getSchedulerService()->register([
                'name' => $jobName,
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => intval(time() + 10),
                'class' => 'Biz\MultiClass\Job\CloneMultiClassJob',
                'args' => ['multiClassId' => $id],
                'misfire_threshold' => 60 * 60,
            ]);
        }

        return ['success' => true];
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->service('Scheduler:SchedulerService');
    }
}
