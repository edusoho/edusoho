<?php


namespace ApiBundle\Api\Resource\DefaultCourse;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class DefaultCourse extends AbstractResource
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
            'ids' => array_unique(array_column($courseSets, 'defaultCourseId')),
            'status' => 'published',
            'titleLike' => $request->query->get('titleLike', ''),
            'courseSetTitleLike' => $request->query->get('titleLike', ''),
        ];

        return $this->getCourseService()->searchDefaultCourses($conditions);
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