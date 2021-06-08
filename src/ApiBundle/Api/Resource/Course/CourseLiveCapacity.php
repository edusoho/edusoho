<?php


namespace ApiBundle\Api\Resource\Course;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Util\EdusohoLiveClient;

class CourseLiveCapacity extends AbstractResource
{
    public function search(ApiRequest $request, $courseId)
    {
        $client = new EdusohoLiveClient();
        return $client->getCapacity();
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}