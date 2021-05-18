<?php

namespace ApiBundle\Api\Resource\MultiClassProduct;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;

class MultiClassProductStudent extends AbstractResource
{
    public function search(ApiRequest $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $conditions = $request->query->all();
        $conditions['courseId'] = $courseId;
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
        $assistantMembers = $this->getCourseMemberService()->searchMembers(['courseId' => $courseId, 'role' => 'assistant'], [], 0, $maxAssistantsCount);
        $assistantIds = ArrayToolkit::column($assistantMembers, 'userId');

        $assistants = $this->getUserService()->findUsersByIds($assistantIds);
        $assistantInfos = ArrayToolkit::thin($assistants, ['id', 'nickname']);

        foreach ($members as &$member) {
            $member['assistants'] = $assistantInfos;
        }

        $members = $this->getThreadService()->fillThreadCounts(['courseId' => $courseId, 'type' => 'question'], $members);

        $homeworkCount = $this->getActivityService()->count(
            ['mediaType' => 'homework', 'fromCourseId' => $courseId]
        );
        $testpaperCount = $this->getActivityService()->count(
            ['mediaType' => 'testpaper', 'fromCourseId' => $courseId]
        );

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    private function getCourseService()
    {
        return $this->service('Course:CourseService');
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
}
