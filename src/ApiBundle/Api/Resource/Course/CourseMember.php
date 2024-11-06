<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LearningDataAnalysisService;
use Biz\Course\Service\MemberService;
use Biz\Exception\UnableJoinException;
use Biz\MemberOperation\Service\MemberOperationService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourseMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $conditions = $request->query->all();
        $conditions['courseId'] = $courseId;
        $conditions['locked'] = 0;
        if (isset($conditions['userKeyword']) && $conditions['userKeyword'] != '') {
            $conditions['userIds'] = $this->getUserService()->getUserIdsByKeyword($conditions['userKeyword']);
        }
        unset($conditions['userKeyword']);

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getMemberService()->searchMembers(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );
        $members = $this->getLearningDataAnalysisService()->fillCourseProgress($members);
        $members = $this->convertJoinedChannel($members);

        $total = $this->getMemberService()->countMembers($conditions);

        $this->getOCUtil()->multiple($members, ['userId']);

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $userId)
    {
        $courseMember = $this->getMemberService()->getCourseMember($courseId, $userId);
        $this->getOCUtil()->single($courseMember, ['userId']);

        return $courseMember;
    }

    public function add(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $member = $this->getMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());

        if (!$member) {
            $member = $this->tryJoin($course);
        }

        if ($member) {
            $this->getOCUtil()->single($member, ['userId']);

            return $member;
        }

        return null;
    }

    private function tryJoin($course)
    {
        try {
            $this->getCourseService()->tryFreeJoin($course['id']);
        } catch (UnableJoinException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e, $e->getCode());
        }

        $member = $this->getMemberService()->getCourseMember($course['id'], $this->getCurrentUser()->getId());
        if (!empty($member)) {
            $this->getLogService()->info('course', 'join_course', "加入 教学计划《{$course['title']}》", ['courseId' => $course['id'], 'title' => $course['title'] ? $course['title'] : $course['courseSetTitle']]);
        }

        return $member;
    }

    private function convertJoinedChannel($members)
    {
        foreach ($members as &$member) {
            if ('import_join' === $member['joinedChannel']) {
                $records = $this->getMemberOperationService()->searchRecords(['target_type' => 'course', 'target_id' => $member['courseId'], 'user_id' => $member['userId'], 'operate_type' => 'join'], ['id' => 'DESC'], 0, 1);
                if (!empty($records)) {
                    $operator = $this->getUserService()->getUser($records[0]['operator_id']);
                    $member['joinedChannelText'] = "{$operator['nickname']}添加";
                }
            } else {
                $member['joinedChannelText'] = ['free_join' => '免费加入', 'buy_join' => '购买加入', 'vip_join' => '会员加入'][$member['joinedChannel']] ?? '';
            }
        }

        return $members;
    }

    /**
     * @return \Biz\System\Service\Impl\LogServiceImpl
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    /**
     * @return MemberService
     */
    private function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MemberOperationService
     */
    private function getMemberOperationService()
    {
        return $this->service('MemberOperation:MemberOperationService');
    }
}
