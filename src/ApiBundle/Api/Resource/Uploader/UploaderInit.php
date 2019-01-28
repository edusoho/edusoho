<?php

namespace ApiBundle\Api\Resource\Uploader;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\System\SettingException;

class UploaderInit extends AbstractResource
{
    public function get(ApiRequest $request, $targetType, $targetId)
    {
        $cloudAttachment = $this->getSettingService()->get('cloud_attachment', array());
        if (!($cloudAttachment['course'])) {
            throw SettingException::FORBIDDEN_CLOUD_ATTACHMENT();
        }

        $user = $this->getCurrentUser();
        $params = array(
            'targetType' => $targetType,
            'targetId' => $targetId,
            'userId' => $user['id'],
            'bucket' => 'private',
        );
        $params = array_merge($request->query->all(), $params);

        if (!$params) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $result = $this->getUploadFileService()->initFormUpload($params);

        return $result;
    }

    /**
     * @return \Biz\System\Service\Impl\SettingServiceImpl
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
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
