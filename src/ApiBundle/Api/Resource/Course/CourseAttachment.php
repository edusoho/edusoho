<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourseAttachment extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $fileId)
    {
        $params = $request->query->all();
        if (!ArrayToolkit::requireds($params, array('targetType', 'targetId'))) {
            throw new BadRequestHttpException('缺少参数', null, '500');
        }
        $this->getCourseService()->tryTakeCourse($courseId);
        $fileUseds = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType($params['targetType'], $params['targetId'], 'attachment', false);

        if (!in_array($fileId, ArrayToolkit::column($fileUseds, 'fileId'))) {
            throw new BadRequestHttpException('该文件没有被引用', null, '404');
        }

        $download = $this->getUploadFileService()->getDownloadMetas($fileId);

        return $download['url'];
    }

    /**
     * @return \Biz\File\Service\Impl\UploadFileServiceImpl
     */
    protected function getUploadFileService()
    {
        return $this->service('File:UploadFileService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
