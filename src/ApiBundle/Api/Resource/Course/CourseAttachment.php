<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\File\UploadFileException;

class CourseAttachment extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $fileId)
    {
        $params = $request->query->all();
        if (!ArrayToolkit::requireds($params, array('targetType', 'targetId'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $this->getCourseService()->tryTakeCourse($courseId);
        $fileUseds = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType($params['targetType'], $params['targetId'], 'attachment', false);

        if (!in_array($fileId, ArrayToolkit::column($fileUseds, 'fileId'))) {
            throw UploadFileException::NOTFOUND_FILE();
        }

        $download = $this->getUploadFileService()->getDownloadMetas($fileId);

        return array('url' => $download['url']);
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
