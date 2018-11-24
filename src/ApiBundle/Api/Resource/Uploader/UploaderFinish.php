<?php

namespace ApiBundle\Api\Resource\Uploader;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Util\UploaderToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UploaderFinish extends AbstractResource
{
    public function get(ApiRequest $request, $type, $courseId)
    {
        $maker = new UploaderToken();
        $token = $maker->make($type, $courseId, 'private', 86400);
        $params = $this->parseToken($token);

//        if (!$params) {
//            return $this->createJsonResponse(array('error' => '上传授权码不正确，请重试！'));
//        }

        $params = array_merge($request->query->all(), $params);

        $file = $this->getUploadFileService()->finishedUpload($params);

        return $file;
    }

    protected function parseToken($token)
    {
        $parser = new UploaderToken();
        $params = $parser->parse($token);

        return $params;
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
