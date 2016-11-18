<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Common\Exception\ResourceNotFoundException;

class SubtitleController extends BaseController
{
    public function manageAction(Request $request)
    {
        $courseId = $request->query->get('courseId');
        $lessonId = $request->query->get('lessonId');

        if (empty($courseId) || empty($lessonId)) {
            throw new InvalidArgumentException("courseId或 lessonId不能为空");
        }

        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        if (!in_array($lesson['type'], array('video', 'audio')) || empty($lesson['mediaId'])) {
            throw new ResourceNotFoundException('lesson', $lessonId);
        }

        $mediaId = $lesson['mediaId'];
        $media   = $this->getUploadFileService()->getFile($mediaId);
        if (empty($media) || !in_array($media['type'], array('video', 'audio'))) {
            throw new ResourceNotFoundException('uploadFile', $mediaId);
        }

        return $this->render('TopxiaWebBundle:Subtitle:manage.html.twig', array(
            'courseId' => $courseId,
            'media'  => $media
        ));
    }

    /**
     * 获取某一视频下所有的字幕
     */
    public function mediaSubtitlesAction(Request $request, $mediaId)
    {
        $courseId = $request->query->get('courseId');
        if (empty($courseId)) {
            throw new InvalidArgumentException("courseId不能为空");
        }
        $this->getCourseService()->tryManageCourse($courseId);

        $subtitles = $this->getSubtitleService()->findSubtitlesByMediaId($mediaId);
        
        return $this->createJsonResponse(array(
            'subtitles' => $subtitles
        ));
    }

    public function createAction(Request $request)
    {
        $courseId = $request->query->get('courseId');
        if (empty($courseId)) {
            throw new InvalidArgumentException("courseId不能为空");
        }
        $this->getCourseService()->tryManageCourse($courseId);

        $fileds = $request->request->all();

        $this->getSubtitleService()->addSubtitle($fileds);

        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $id)
    {
        $courseId = $request->query->get('courseId', 0);
        $course   = $this->getCourseService()->tryManageCourse($courseId);

        $this->getSubtitleService()->deleteSubtitle($id);

        return $this->createJsonResponse(true);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function getSubtitleService()
    {
        return $this->createService('Subtitle.SubtitleService');
    }
}
