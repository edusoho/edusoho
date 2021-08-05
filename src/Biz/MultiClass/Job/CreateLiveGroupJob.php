<?php

namespace Biz\MultiClass\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\LogService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CreateLiveGroupJob extends AbstractJob
{
    private $client;

    public function execute()
    {
        $multiClassId = $this->args['multiClassId'];
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            return;
        }

        $groups = $this->getMultiClassGroupService()->findGroupsByMultiClassId($multiClassId);
        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($multiClass['courseId'], 'live', true);
        foreach ($activities as $activity) {
            if (empty($activity['ext'])) {
                continue;
            }

            $liveGroups = $this->getEduSohoLiveClient()->batchCreateLiveGroups([
                'liveId' => $activity['ext']['liveId'],
                'groupNames' => ArrayToolkit::column($groups, 'name')
            ]);

            $createLiveGroups = [];
            foreach ($groups as $key => $group) {
                $createLiveGroups[] = [
                    'group_id' => $group['id'],
                    'live_id' => $activity['ext']['liveId'],
                    'live_code' => $liveGroups[$key]['code']
                ];
            }

            $this->getMultiClassGroupService()->batchCreateLiveGroups($createLiveGroups);
        }
    }

    protected function getEduSohoLiveClient()
    {
        if (empty($this->client)) {
            $this->client = new EdusohoLiveClient();
        }

        return $this->client;
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->biz->service('MultiClass:MultiClassService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->biz->service('Assistant:AssistantStudentService');
    }

    /**
     * @return MultiClassGroupService
     */
    private function getMultiClassGroupService()
    {
        return $this->biz->service('MultiClass:MultiClassGroupService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }
}
