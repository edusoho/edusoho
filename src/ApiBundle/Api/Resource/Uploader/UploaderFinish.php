<?php

namespace ApiBundle\Api\Resource\Uploader;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UploaderFinish extends AbstractResource
{
    public function get(ApiRequest $request, $type, $courseId)
    {
        $cloudAttachment = $this->getSettingService()->get('cloud_attachment', array());
        if (!($cloudAttachment['course'])) {
            throw new BadRequestHttpException('云附件未开启', null, 4031601);
        }

        $params = $request->query->all();

        $file = $this->getUploadFileService()->finishedUpload($params);

        return $file;
    }

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

    /**
     * @return \Biz\Course\Service\Impl\ThreadServiceImpl
     */
    protected function getCourseThreadService()
    {
        return $this->service('Course:ThreadService');
    }
}
