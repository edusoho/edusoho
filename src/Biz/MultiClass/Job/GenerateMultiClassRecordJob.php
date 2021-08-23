<?php

namespace Biz\MultiClass\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class GenerateMultiClassRecordJob extends AbstractJob
{
    public function execute()
    {
        $multiClassId = $this->args['multiClassId'];
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            return;
        }

        $members = $this->getCourseMemberService()->findMembersByCourseIdAndRole($multiClass['courseId'], 'student');
        $relations = $this->getAssistantStudentService()->findByMultiClassId($multiClassId);
        $relations = ArrayToolkit::index($relations, 'studentId');
        $assistants = $this->getUserService()->findUsersByIds(ArrayToolkit::column($relations, 'assistantId'));
        $records = [];
        foreach ($members as $member) {
            if ($relations[$member['userId']]) {
                continue;
            }

            $assistant = $assistants[$relations[$member['userId']]['assistantId']];
            $records[] = [
                'user_id' => $member['userId'],
                'assistant_id' => $relations[$member['userId']]['assistantId'],
                'multi_class_id' => $multiClassId,
                'data' => json_encode(['title' => '加入班课', 'content' => sprintf('加入班课(%s), 分配助教(%s)', $multiClass['title'], $assistant['nickname'])]),
                'sign' => $this->getMultiClassRecordService()->makeSign(),
                'is_push' => 0,
            ];
        }

        return $this->getMultiClassRecordService()->batchCreateRecords($records);
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
     * @return MultiClassRecordService
     */
    private function getMultiClassRecordService()
    {
        return $this->biz->service('MultiClass:MultiClassRecordService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
