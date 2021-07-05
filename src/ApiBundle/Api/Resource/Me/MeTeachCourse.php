<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;

class MeTeachCourse extends AbstractResource
{
    /**
     * @return array
     */
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        $conditions = [
            'parentId' => 0,
            'status' => 'published',
            'excludeTypes' => ['reservation'],
        ];

        if ($request->query->get('types')) {
            $conditions['types'] = $request->query->get('types');
        }

        if (!in_array('ROLE_ADMIN', $user->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $members = $this->getMemberService()->findMembersByUserIdAndRoles($user['id'], ['teacher']);
            $conditions['ids'] = empty($members) ? [-1] : ArrayToolkit::column($members, 'courseSetId');
        }

        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, ['createdTime' => 'desc'], 0, PHP_INT_MAX);

        $conditions = [
            'courseSetIds' => empty($courseSets) ? [-1] : array_column($courseSets, 'id'),
            'status' => 'published',
            'courseSetTitleLike' => $request->query->get('titleLike', ''),
        ];

        if ($request->query->get('isDefault')) {
            $conditions['isDefault'] = 1;
        }

        $multiClasses = $this->getMultiClassService()->findAllMultiClass();
        if (!empty($multiClasses)) {
            $conditions['excludeIds'] = array_column($multiClasses, 'courseId');
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $courses = $this->getCourseService()->searchCourses($conditions, ['createdTime' => 'desc'], $offset, $limit, ['id', 'title', 'courseSetTitle', 'courseSetId']);
        $total = $this->getCourseService()->countCourses($conditions);

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }
}
