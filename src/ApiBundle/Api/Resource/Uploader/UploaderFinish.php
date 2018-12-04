<?php

namespace ApiBundle\Api\Resource\Uploader;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UploaderFinish extends AbstractResource
{
    public function get(ApiRequest $request, $type, $fileId)
    {
        $cloudAttachment = $this->getSettingService()->get('cloud_attachment', array());
        if (!($cloudAttachment['course'])) {
            throw new BadRequestHttpException('云附件未开启', null, 4031601);
        }

        $file = $this->getUploadFileService()->getUploadFileInit($fileId);
        if (empty($file)) {
            throw new BadRequestHttpException('上传文件不存在', null, 4042001);
        }

        $params = $request->query->all();
        $params['size'] = isset($params['fileSize']) ? $params['fileSize'] : 0;
        $params['id'] = $fileId;

        if (empty($params)) {
            throw new BadRequestHttpException('参数错误', null, 5000306);
        }

        $file = $this->getUploadFileService()->finishedUpload($params);

        return $file;
    }

    /**
     * @return \Biz\File\Service\Impl\UploadFileServiceImpl
     */
    protected function getUploadFileService()
    {
        return $this->service('File:UploadFileService');
    }

    /**
     * @return \Biz\System\Service\Impl\SettingServiceImpl
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
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
