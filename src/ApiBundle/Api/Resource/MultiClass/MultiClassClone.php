<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
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
        $cloneMultiClass = $this->checkDataFields($request->request->all());

        $existed = $this->getMultiClassService()->getMultiClassByTitle($cloneMultiClass['title']);
        if ($existed) {
            throw MultiClassException::MULTI_CLASS_EXIST();
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
                'args' => ['multiClassId' => $id, 'cloneMultiClass' => $cloneMultiClass],
                'misfire_threshold' => 60 * 60,
            ]);
        }

        return ['success' => true];
    }

    private function checkDataFields($multiClass)
    {
        if (!ArrayToolkit::requireds($multiClass, ['title', 'courseSetTitle', 'productId'])) {
            throw MultiClassException::MULTI_CLASS_DATA_FIELDS_MISSING();
        }

        if (empty($multiClass['teacherId'])) {
            throw MultiClassException::MULTI_CLASS_TEACHER_REQUIRE();
        }

        if (empty($multiClass['assistantIds'])) {
            throw MultiClassException::MULTI_CLASS_ASSISTANT_REQUIRE();
        }

        if (count($multiClass['assistantIds']) > MultiClass::MAX_ASSISTANT_NUMBER) {
            throw MultiClassException::MULTI_CLASS_ASSISTANT_NUMBER_EXCEED();
        }

        if (in_array($multiClass['teacherId'], $multiClass['assistantIds'])) {
            throw MultiClassException::MULTI_CLASS_TEACHER_CANNOT_BE_ASSISTANT();
        }

        return $multiClass;
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
