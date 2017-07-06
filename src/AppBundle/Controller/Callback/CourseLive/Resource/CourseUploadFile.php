<?php

namespace AppBundle\Controller\Callback\CourseLive\Resource;

use AppBundle\Controller\Callback\CourseLive\BaseProvider;
use Symfony\Component\HttpFoundation\Request;

class CourseUploadFile extends BaseProvider
{
    public function post(Request $request)
    {
        $token = $request->query->get('token');
        $courseId = $request->query->get('courseId');
        // $this->checkToken($token);

        // $userToken = $this->getTokenService()->getByToken($token);
        // if ($userToken['data'] != $courseId) {
        //     throw new \RuntimeException(sprintf('token course id 不匹配', $userToken['data']));
        // }
        $course = $this->getCourseService()->getCourse($courseId);

        $file['globalId'] = $request->request->get('globalId');
        $file['key'] = $request->request->get('hashId');
        $file['filename'] = $request->request->get('filename');
        $file['size'] = $request->request->get('size');
        $file['length'] = $request->request->get('length', 0);
        $file['convertHash'] = $file['key'];
        $file['convertParams'] = array();
        $file['lazyConvert'] = false;

        try {
            $this->getUploadFileService()->addFile('coursematerial', $course['courseSetId'], $file, 'cloud');
        } catch (\Exception $e) {
            return array(
                'error' => array(
                    'code' => -1,
                    'message' => sprintf('添加文件出错: %s', $e->getMessage()),
                ),
            );
        }

        return array(
            'success' => true,
        );
    }

    protected function checkToken($token)
    {
        $isTrue = $this->getTokenService()->verifyToken('live.create', $token);

        if (!$isTrue) {
            throw new \RuntimeException('Token不正确！');
        }
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
