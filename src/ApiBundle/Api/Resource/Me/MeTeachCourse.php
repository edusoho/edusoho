<?php


namespace ApiBundle\Api\Resource\Me;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use ApiBundle\Api\Annotation\Access;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;

class MeTeachCourse extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @return array
     * @Access(roles="ROLE_TEACHER_ASSISTANT,ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        $members = $this->getMemberService()->findMembersByUserIdAndRoles($user['id'], ['teacher']);

        $conditions = [
            'parentId' => 0,
            'status' => 'published',
            'ids' => empty($members) ? [-1] : ArrayToolkit::column($members, 'courseSetId'),
        ];

        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, [], 0, PHP_INT_MAX);

        $conditions = [
            'courseSetIds' => empty($courseSets) ? [-1] : array_column($courseSets, 'id'),
            'status' => 'published',
            'courseSetTitleLike' => $request->query->get('titleLike', ''),
        ];

        if ($request->query->get('isDefault')) {
            $conditions['isDefault'] = 1;
        }

        $multiClasses = $this->getMultiClassService()->findMultiClassesByCourseIds(ArrayToolkit::column($members, 'courseId'));
        if (!empty($multiClasses)) {
            $conditions['excludeIds'] = array_column($multiClasses, 'courseId');
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $courses = $this->getCourseService()->searchCourses($conditions, [], $offset, $limit, ['id', 'title', 'courseSetTitle', 'courseSetId']);
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