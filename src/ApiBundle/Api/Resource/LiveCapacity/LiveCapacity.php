<?php


namespace ApiBundle\Api\Resource\LiveCapacity;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Util\EdusohoLiveClient;

class LiveCapacity extends AbstractResource
{
    public function search(ApiRequest $request)
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