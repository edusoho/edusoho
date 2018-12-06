<?php

namespace AppBundle\Controller\Callback\ESLive;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\File\Service\UploadFileService;
use Biz\User\Service\TokenService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Callback extends ESLiveBase
{
    public function fetch(Request $request)
    {
        $token = $request->query->get('token');
        $context = $this->getJWTAuth()->valid($token);
        if (!$context) {
            throw new BadRequestHttpException('Token Error');
        }

        $module = $request->query->get('module', 'file');
        $method = 'fetch'.$module;

        return $this->$method($request, $context);
    }

    protected function fetchFile(Request $request, $context)
    {
        $sourceFrom = $request->query->get('type', '');
        $keyword = $request->query->get('keyword', '');
        $fileType = $request->query->get('fileType', '');
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);
        $conditions = array(
            'filenameLike' => $keyword,
            'storage' => 'cloud',
        );
        if ($fileType && in_array($fileType, array('video'))) {
            $conditions['type'] = $fileType;
        }
        $cloudFiles = array(
            'data' => array(),
        );
        if (empty($sourceFrom) || 'open_course' == $sourceFrom || !in_array($sourceFrom, $context['sources'])) {
            goto end;
        }

        if ('course' == $sourceFrom && empty($context['courseId'])) {
            goto end;
        }

        switch ($sourceFrom) {
            case 'course':
                $course = $this->getCourseService()->getCourse($context['courseId']);
                $conditions['targetId'] = $course['courseSetId'];
                $conditions['targetType'] = 'coursematerial';
                break;
            case 'my':
                $conditions['createdUserId'] = $context['userId'] ?: -1;
                break;
            case 'public':
                $conditions['isPublic'] = 1;
                break;
            default:
                break;
        }
        $filesCount = $this->getUploadFileService()->countUploadFiles($conditions);

        $files = $this->getUploadFileService()->searchUploadFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($files, 'createdUserId') ?: array(-1);

        $users = $this->getUserService()->searchUsers(
            array('ids' => $userIds),
            array(),
            0,
            PHP_INT_MAX,
            array('id', 'nickname')
        );
        foreach ($files as $file) {
            $cloudFile['filename'] = $file['filename'];
            $cloudFile['type'] = $file['type'];
            $cloudFile['size'] = $file['fileSize'];
            $cloudFile['length'] = $file['length'];
            $cloudFile['album'] = empty($course) ? '' : $course['courseSetTitle'];
            $cloudFile['created_at'] = $file['fileSize'];
            $cloudFile['owner'] = empty($users[$file['createdUserId']]) ? '' : $users[$file['createdUserId']]['nickname'];

            $biz = $this->getBiz();
            $cloudFile['play'] = array(
                'url' => $biz['qiQiuYunSdk.playv2']->makePlayMetaUrl($file['globalId'], array('std' => 1), 36000),
            );

            $cloudFiles['data'][] = $cloudFile;
        }
        end:
        $cloudFiles['paging'] = array(
            'total' => isset($filesCount) ? $filesCount : 0,
            'start' => $start,
            'limit' => $limit,
        );

        return $cloudFiles;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
