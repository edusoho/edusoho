<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\Role\Service\RoleService;
use Biz\User\Service\UserFieldService;
use Biz\User\UserException;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassStudent extends AbstractResource
{
    /**
     * @param $multiClassId
     *
     * @return bool[]
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_TEACHER")
     */
    public function add(ApiRequest $request, $multiClassId)
    {
        $studentData = $request->request->all();
        if (!ArrayToolkit::requireds($studentData, ['userInfo', 'price'])) {
            throw MultiClassException::MULTI_CLASS_DATA_FIELDS_MISSING();
        }

        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $courseId = $multiClass['courseId'];
        $studentData['source'] = 'outside';
        $operateUser = $this->getCurrentUser();
        $studentData['remark'] = empty($studentData['remark']) ? $operateUser['nickname'].'添加' : $studentData['remark'];
        $user = $this->getUserService()->getUserByLoginField($studentData['userInfo'], true);

        $this->getCourseMemberService()->becomeStudentAndCreateOrder($user['id'], $courseId, $studentData);

        return ['success' => true];
    }

    /**
     * get api/multi_class/{id}/students
     */
    public function search(ApiRequest $request, $id)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($id);
        $conditions = $request->query->all();
        $conditions['courseId'] = $multiClass['courseId'];
        $conditions['role'] = 'student';

        if (!empty($conditions['keyword'])) {
            $conditions['userIds'] = $this->getUserService()->getUserIdsByKeyword($keyword);
            unset($conditions['keyword']);
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );

        $total = $this->getCourseMemberService()->countMembers($conditions);

        $this->getOCUtil()->multiple($members, ['userId'], 'user', 'user', true);
        $this->getOCUtil()->multiple($members, ['userId'], 'profile', 'profile', true);

        $members = $this->getLearningDataAnalysisService()->fillCourseProgress($members);

        $maxAssistantsCount = 20;
        $assistantMembers = $this->getCourseMemberService()->searchMembers(['courseId' => $multiClass['courseId'], 'role' => 'assistant'], [], 0, $maxAssistantsCount);
        $assistantIds = ArrayToolkit::column($assistantMembers, 'userId');

        $assistants = $this->getUserService()->findUsersByIds($assistantIds);
        $assistantInfos = ArrayToolkit::thin(array_values($assistants), ['id', 'nickname']);

        $members = $this->getThreadService()->fillThreadCounts(['courseId' => $multiClass['courseId'], 'type' => 'question'], $members);

        $homeworkCount = $this->getActivityService()->count(
            ['mediaType' => 'homework', 'fromCourseId' => $multiClass['courseId']]
        );
        $testpaperCount = $this->getActivityService()->count(
            ['mediaType' => 'testpaper', 'fromCourseId' => $multiClass['courseId']]
        );

        $userHomeworkCount = $this->findUserTaskCount($multiClass['courseId'], 'homework');
        $userTestpaperCount = $this->findUserTaskCount($multiClass['courseId'], 'testpaper');
        foreach ($members as &$member) {
            $member['assistants'] = $assistantInfos;
            $member['finishedHomeworkCount'] = 0;
            $member['homeworkCount'] = $homeworkCount;
            if (!empty($userHomeworkCount[$member['userId']])) {
                $member['finishedHomeworkCount'] = $userHomeworkCount[$member['userId']];
            }

            $member['finishedTestpaperCount'] = 0;
            $member['testpaperCount'] = $testpaperCount;
            if (!empty($userTestpaperCount[$member['userId']])) {
                $member['finishedTestpaperCount'] = $userTestpaperCount[$member['userId']];
            }
        }

        $members = $this->filterFields($members);

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    public function remove(ApiRequest $request, $id, $userId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($id);
    }

    private function findUserTaskCount($courseId, $type, $count)
    {
        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, $type, true);

        $userTaskCount = [];
        foreach ($activities as $activity) {
            if (empty($activity['ext']['answerSceneId'])) {
                continue;
            }

            $answerReports = $this->getAnswerReportService()->search(
                ['answer_scene_id' => $activity['ext']['answerSceneId']],
                [],
                0,
                $this->getAnswerReportService()->count(['answer_scene_id' => $activity['ext']['answerSceneId']])
            );

            $answerReports = ArrayToolkit::group($answerReports, 'user_id');

            foreach ($answerReports as $userId => $answerReport) {
                if (empty($userTaskCount[$userId])) {
                    $userTaskCount[$userId] = 0;
                }

                ++$userTaskCount[$userId];
            }
        }

        return $userTaskCount;
    }

    private function filterFields($members)
    {
        $results = [];
        foreach ($members as $member) {
            $filteredFields = ArrayToolkit::parts($member, [
                'id',
                'learningProgressPercent',
                'threadCount',
                'homeworkCount',
                'finishedHomeworkCount',
                'testpaperCount',
                'finishedTestpaperCount',
                'deadline',
                'createdTime',
            ]);

            if (empty($filteredFields['deadline'])) {
                unset($filteredFields['deadline']);
            }

            $filteredFields['user'] = [
                'id' => $member['userId'],
                'nickname' => $member['user']['nickname'],
                'verifiedMobile' => $member['user']['verifiedMobile'],
                'weixin' => $member['profile']['weixin'],
            ];

            $filteredFields['assistants'] = ArrayToolkit::thin($member['assistants'], ['id', 'nickname']);

            $results[] = $filteredFields;
        }

        return $results;
    }

    /**
     * @param ApiRequest $request
     * @param $multiClassId
     * @param $userId
     * @return array
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_TEACHER,ROLE_TEACHER_ASSISTANT")
     */
    public function get(ApiRequest $request, $multiClassId, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)){
            throw UserException::NOTFOUND_USER();
        }

        if (1 == $user['destroyed']) {
            throw UserException::USER_IS_DESTROYED();
        }

        $user['roles'] = $this->getRoleService()->findRolesByCodes($user['roles']);

        $profile = $this->getUserService()->getUserProfile($userId);
        $profile['title'] = $user['title'];

        $fields = $this->getFields();

        return ['user' => $user, 'profile' => $profile, 'fields' => $fields];
    }

    protected function getFields()
    {
        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0; $i < count($fields); ++$i) {
            if (strstr($fields[$i]['fieldName'], 'textField')) {
                $fields[$i]['type'] = 'text';
            }

            if (strstr($fields[$i]['fieldName'], 'varcharField')) {
                $fields[$i]['type'] = 'varchar';
            }

            if (strstr($fields[$i]['fieldName'], 'intField')) {
                $fields[$i]['type'] = 'int';
            }

            if (strstr($fields[$i]['fieldName'], 'floatField')) {
                $fields[$i]['type'] = 'float';
            }

            if (strstr($fields[$i]['fieldName'], 'dateField')) {
                $fields[$i]['type'] = 'date';
            }
        }

        return $fields;
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->service('Role:RoleService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->service('User:UserFieldService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    private function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    private function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    private function getThreadService()
    {
        return $this->service('Course:ThreadService');
    }

    private function getLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }

    /**
     * @return MultiClassService
     */
    private function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }
}
