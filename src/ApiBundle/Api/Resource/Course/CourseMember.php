<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Exception\UnableJoinException;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->service('Course:MemberService')->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $offset,
            $limit
        );

        $total = $this->service('Course:MemberService')->countMembers($conditions);

        $this->getOCUtil()->multiple($members, array('userId'));

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $userId)
    {
        $courseMember = $this->getMemberService()->getCourseMember($courseId, $userId);
        $this->getOCUtil()->single($courseMember, array('userId'));

        return $courseMember;
    }

    public function add(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw new NotFoundHttpException('教学计划不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }
        $this->dispatchEvent('test', new Event());

        $member = $this->getMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());


        if (!$member) {
            $member = $this->tryJoin($course);
        }

        if ($member) {
            $this->getOCUtil()->single($member, array('userId'));

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

        return $this->getMemberService()->getCourseMember($course['id'], $this->getCurrentUser()->getId());
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
}
