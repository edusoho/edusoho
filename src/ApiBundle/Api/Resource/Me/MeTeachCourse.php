<?php


namespace ApiBundle\Api\Resource\Me;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class MeTeachCourse extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        $conditions = [
            'parentId' => 0,
            'status' => 'published',
        ];

        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets($user['id'], $conditions, 0, PHP_INT_MAX);

        $conditions = [
            'courseSetIds' => array_column($courseSets, 'id'),
            'status' => 'published',
            'courseSetTitleLike' => $request->query->get('titleLike', ''),
            'isDefault' => $request->query->get('isDefault', 1)
        ];

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $courses = $this->getCourseService()->searchCourses($conditions, [], $offset, $limit, ['id', 'title', 'courseSetTitle']);
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
}