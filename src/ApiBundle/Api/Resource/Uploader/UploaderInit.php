<?php

namespace ApiBundle\Api\Resource\Uploader;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Util\UploaderToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UploaderInit extends AbstractResource
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

        $params['uploadCallback'] = $this->generateUrl('uploader_upload_callback', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $params['processCallback'] = $this->generateUrl('uploader_process_callback', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        $result = $this->getUploadFileService()->initUpload($params);

        $result['uploadProxyUrl'] = $this->generateUrl('uploader_entry', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $result['authUrl'] = $this->generateUrl('uploader_auth', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        return $result;
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
