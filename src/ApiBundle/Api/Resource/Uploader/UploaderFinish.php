<?php

namespace ApiBundle\Api\Resource\Uploader;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\File\UploadFileException;
use Biz\System\SettingException;

class UploaderFinish extends AbstractResource
{
    public function get(ApiRequest $request, $type, $fileId)
    {
        $cloudAttachment = $this->getSettingService()->get('cloud_attachment', array());
        if (!($cloudAttachment['course'])) {
            throw SettingException::FORBIDDEN_CLOUD_ATTACHMENT();
        }

        $file = $this->getUploadFileService()->getUploadFileInit($fileId);
        if (empty($file)) {
            throw UploadFileException::NOTFOUND_FILE();
        }

        $params = $request->query->all();
        $params['id'] = $fileId;
        $params['uploadType'] = 'direct';

        if (empty($params)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
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
