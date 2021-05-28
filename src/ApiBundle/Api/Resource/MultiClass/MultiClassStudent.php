<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;

class MultiClassStudent extends AbstractResource
{
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

        $userHomeworkCount = $this->findUserTaskCount($multiClass['courseId'], 'homework', $homeworkCount);
        $userTestpaperCount = $this->findUserTaskCount($multiClass['courseId'], 'testpaper', $testpaperCount);
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
        $tasks = $this->getTaskService()->searchTasks(
            ['courseId' => $courseId, 'type' => $type],
            ['seq' => 'ASC', 'id' => 'ASC'],
            0,
            $count
        );

        list($tasks, $testpapers) = $this->getTaskService()->findTestpapers($tasks, $type);

        $userTaskCount = [];
        foreach ($tasks as $task) {
            if (empty($task['answerSceneId'])) {
                continue;
            }

            $answerReports = $this->getAnswerReportService()->search(
                ['answer_scene_id' => $task['answerSceneId']],
                [],
                0,
                $this->getAnswerReportService()->count(['answer_scene_id' => $task['answerSceneId']])
            );

            foreach ($answerReports as $answerReport) {
                if (empty($userTaskCount[$answerReport['user_id']])) {
                    $userTaskCount[$answerReport['user_id']] = 0;
                }

                ++$userTaskCount[$answerReport['user_id']];
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
