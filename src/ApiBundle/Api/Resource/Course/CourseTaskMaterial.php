<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\MaterialException;
use Biz\Course\MemberException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\TokenService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CourseTaskMaterial extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $taskId, $materialId)
    {
        $canLearn = $this->getCourseService()->canLearnTask($taskId);

        if ('success' != $canLearn['code']) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);

        if (empty($material)) {
            throw MaterialException::NOTFOUND_MATERIAL();
        }

        $tokenFields = array(
            'data' => array(
                'userId' => $this->getCurrentUser()->getId(),
                'courseId' => $courseId,
                'taskId' => $taskId,
                'materialId' => $materialId,
                'fileId' => $material['fileId'],
            ),
            'times' => 0,
            'userId' => $this->getCurrentUser()->getId(),
            'duration' => 60 * 60,
        );
        $token = $this->getTokenService()->makeToken('file_download', $tokenFields);

        $url = $this->generateUrl('course_task_token_download', array(
            'courseId' => $courseId,
            'taskId' => $taskId,
            'token' => $token['token'],
        ), UrlGeneratorInterface::ABSOLUTE_URL);

        return array(
            'url' => $url,
        );
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->service('Course:MaterialService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
