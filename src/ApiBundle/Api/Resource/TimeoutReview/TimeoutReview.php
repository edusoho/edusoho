<?php

namespace ApiBundle\Api\Resource\TimeoutReview;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Activity\Service\ActivityService;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class TimeoutReview extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_education')) {
            throw new AccessDeniedException();
        }

        $multiClasses = $this->getMultiClassService()->findAllMultiClass();

        $courseIds = ArrayToolkit::column($multiClasses, 'courseId');
        $activities = $this->getActivityService()->findActivitiesByCourseIdsAndTypes($courseIds, ['homework', 'testpaper'], true);
        $answerSceneIds = [];
        $sceneIndexActivities = [];
        foreach ($activities as $activity) {
            $answerSceneIds[] = $activity['ext']['answerSceneId'];
            $sceneIndexActivities[$activity['ext']['answerSceneId']] = $activity;
        }

        $conditions = [
            'answer_scene_ids' => empty($answerSceneIds) ? [-1] : $answerSceneIds,
            'status' => 'reviewing',
        ];
        $reviewTimeLimit = $this->getSettingService()->node('multi_class.review_time_limit', 0);
        if ($reviewTimeLimit) {
            $conditions['endTime_LE'] = time() - $reviewTimeLimit * 3600;
        } else {
            $conditions['ids'] = [-1];
        }
        $total = $this->getAnswerRecordService()->count($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            ['end_time' => 'ASC'],
            $offset,
            $limit
        );

        $answerRecords = $this->filterRecords($answerRecords, $multiClasses, $sceneIndexActivities);

        return $this->makePagingObject($answerRecords, $total, $offset, $limit);
    }

    protected function filterRecords($answerRecords, $multiClasses, $sceneIndexActivities)
    {
        $assessments = ArrayToolkit::index($this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($answerRecords, 'assessment_id')), 'id');
        $multiClasses = ArrayToolkit::index($multiClasses, 'courseId');
        $assistantStudentRelations = ArrayToolkit::index($this->getAssistantStudentService()->findByMultiClassIds(ArrayToolkit::column($multiClasses, 'id')), 'unitKey');
        $studentIds = ArrayToolkit::column($assistantStudentRelations, 'studentId');
        $assistantIds = ArrayToolkit::column($assistantStudentRelations, 'assistantId');
        $userIds = array_merge($assistantIds, ArrayToolkit::column($answerRecords, 'user_id'));
        $userIds = array_values(array_unique($userIds));
        $users = ArrayToolkit::index($this->getUserService()->findUsersByIds($userIds), 'id');
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filters($users);

        foreach ($answerRecords as &$answerRecord) {
            $answerRecord['activity'] = $sceneIndexActivities[$answerRecord['answer_scene_id']];
            $answerRecord['assessment'] = isset($assessments[$answerRecord['assessment_id']]) ? $assessments[$answerRecord['assessment_id']] : [];
            $answerRecord['multiClass'] = isset($multiClasses[$answerRecord['activity']['fromCourseId']]) ? $multiClasses[$answerRecord['activity']['fromCourseId']] : [];
            $answerRecord['userInfo'] = isset($users[$answerRecord['user_id']]) ? $users[$answerRecord['user_id']] : [];
            $unitKey = $answerRecord['multiClass']['id'].'_'.$answerRecord['user_id'];
            $assistantId = isset($assistantStudentRelations[$unitKey]) ? $assistantStudentRelations[$unitKey]['assistantId'] : -1;
            $answerRecord['assistantInfo'] = isset($users[$assistantId]) ? $users[$assistantId] : [];
        }

        return $answerRecords;
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->service('Assistant:AssistantStudentService');
    }
}
