<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\File\Service\UploadFileService;
use Biz\File\UploadFileException;
use Biz\OpenCourse\OpenCourseException;
use Biz\Player\PlayerException;
use Biz\Player\Service\PlayerService;
use Biz\System\Service\SettingService;

class OpenCourseLessonMedia extends AbstractResource
{
    /**
     * @param $courseId
     * @param $leesonId
     *
     * @return array
     * @Access(roles="")
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $lessonId)
    {
        $ssl = $request->getHttpRequest()->isSecure() ? true : false;

        $course = $this->getOpenCourseService()->getCourse($courseId);
        if (empty($course)) {
            throw OpenCourseException::NOTFOUND_OPENCOURSE();
        }

        $lesson = $this->getOpenCourseService()->getLesson($lessonId);
        if (empty($lesson)) {
            throw OpenCourseException::NOTFOUND_LESSON();
        }

        $media = $this->getLessonMedia($request, $lesson, $ssl);

        return [
            'mediaType' => 'video',
            'media' => $media,
            'format' => $request->query->get('format', 'common'),
        ];
    }

    protected function getLessonMedia($request, $lesson, $ssl = false)
    {
        $file = $this->getUploadFileService()->getFullFile($lesson['mediaId']);
        if (empty($file)) {
            throw UploadFileException::NOTFOUND_FILE();
        }
        if (!in_array($file['type'], ['video'])) {
            throw PlayerException::NOT_SUPPORT_TYPE();
        }

        $version = $request->query->get('version', 'qiqiuyun');
        if ('escloud' == $version) {
            return $this->getResourceFacadeService()->getPlayerContext($file);
        }

        $player = $this->getPlayerService()->getAudioAndVideoPlayerType($file);
        $agentInWhiteList = $this->getResourceFacadeService()->agentInWhiteList($request->headers->get('user-agent'));
        $isEncryptionPlus = false;
        $context = [];
        if ('video' == $file['type'] && 'cloud' == $file['storage']) {
            $videoPlayer = $this->getPlayerService()->getVideoFilePlayer($file, $agentInWhiteList, [], $ssl);
            $isEncryptionPlus = $videoPlayer['isEncryptionPlus'];
            $context = $videoPlayer['context'];
            if (!empty($videoPlayer['mp4Url'])) {
                $mp4Url = $videoPlayer['mp4Url'];
            }
        }

        $url = isset($mp4Url) ? $mp4Url : $this->getPlayUrl($file, $context, $ssl);
        $supportMobile = intval($this->getSettingService()->node('storage.support_mobile', 0));

        return [
            'resId' => $file['globalId'],
            'url' => isset($url) ? $url : null,
            'player' => $player,
            'videoHeaderLength' => isset($context['videoHeaderLength']) ? $context['videoHeaderLength'] : 0,
            'timeLimit' => 0,
            'agentInWhiteList' => $agentInWhiteList,
            'isEncryptionPlus' => $isEncryptionPlus,
            'supportMobile' => $supportMobile,
        ];
    }

    protected function getPlayUrl($file, $context, $ssl)
    {
        $result = $this->getPlayerService()->getVideoPlayUrl($file, $context, $ssl);
        if (isset($result['url'])) {
            return $result['url'];
        }

        return $this->generateUrl($result['route'], $result['params'], $result['referenceType']);
    }

    /**
     * @return PlayerService
     */
    protected function getPlayerService()
    {
        return $this->getBiz()->service('Player:PlayerService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    protected function getResourceFacadeService()
    {
        return $this->getBiz()->service('CloudPlatform:ResourceFacadeService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }
}
