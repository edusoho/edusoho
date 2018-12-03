<?php

namespace ApiBundle\Api\Resource\Uploader;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UploaderInit extends AbstractResource
{
    public function get(ApiRequest $request, $targetType, $targetId)
    {
        $cloudAttachment = $this->getSettingService()->get('cloud_attachment', array());
        if (!($cloudAttachment['course'])) {
            throw new BadRequestHttpException('云附件未开启', null, 4031601);
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
            throw new BadRequestHttpException('上传授权码不正确，请重试！', null, 5001609);
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
