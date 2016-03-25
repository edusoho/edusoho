<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseManageController extends BaseController
{
    public function liveOpenTimeSetAction(Request $request, $id)
    {
        $liveCourse     = $this->getCourseService()->tryManageCourse($id);
        $openLiveLesson = $this->getCourseService()->searchLessons(array('courseId' => $liveCourse['id']), array('startTime', 'DESC'), 0, 1);
        $openLiveLesson = $openLiveLesson ? $openLiveLesson[0] : array();

        if ($request->getMethod() == 'POST') {
            $liveLesson = $request->request->all();

            if ($openLiveLesson) {
                $updateLiveLesson['startTime'] = strtotime($liveLesson['startTime']);
                $updateLiveLesson['length']    = $liveLesson['timeLength'];

                $openLiveLesson = $this->getCourseService()->updateLesson($liveCourse['id'], $openLiveLesson['id'], $updateLiveLesson);
            } else {
                $liveLesson['type']      = 'liveOpen';
                $liveLesson['courseId']  = $liveCourse['id'];
                $liveLesson['startTime'] = strtotime($liveLesson['startTime']);
                $liveLesson['length']    = $liveLesson['timeLength'];
                $liveLesson['title']     = $liveCourse['title'];
                $liveLesson['status']    = 'published';

                $live = $this->_createCloudLive($liveCourse, $liveLesson);

                $liveLesson['mediaId']      = $live['id'];
                $liveLesson['liveProvider'] = $live['provider'];
                $liveLesson                 = $this->getCourseService()->createLesson($liveLesson);
            }
        }

        return $this->render('TopxiaWebBundle:OpenCourseManage:live-open-time-set.html.twig', array(
            'course'         => $liveCourse,
            'openLiveLesson' => $openLiveLesson
        ));
    }

    public function marketingAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-marketing.html.twig', array(
            'course' => $course
        ));
    }

    private function _createCloudLive($liveCourse, $formFields)
    {
        $speakerId = current($liveCourse['teacherIds']);
        $speaker   = $speakerId ? $this->getUserService()->getUser($speakerId) : null;
        $speaker   = $speaker ? $speaker['nickname'] : '老师';

        $liveLogo    = $this->getSettingService()->get('course');
        $liveLogoUrl = "";

        if (!empty($liveLogo) && array_key_exists("live_logo", $liveLogo) && !empty($liveLogo["live_logo"])) {
            $liveLogoUrl = $this->getServiceKernel()->getEnvVariable('baseUrl')."/".$liveLogo["live_logo"];
        }

        $client = new EdusohoLiveClient();
        $live   = $client->createLive(array(
            'summary'     => null,
            'title'       => $formFields['title'],
            'speaker'     => $speaker,
            'startTime'   => $formFields['startTime'].'',
            'endTime'     => ($formFields['startTime'] + $formFields['length'] * 60).'',
            'authUrl'     => $this->generateUrl('live_auth', array(), true),
            'jumpUrl'     => $this->generateUrl('live_jump', array('id' => $formFields['courseId']), true),
            'liveLogoUrl' => $liveLogoUrl
        ));

        if (empty($live)) {
            throw new \RuntimeException('创建直播教室失败，请重试！');
        }

        if (isset($live['error'])) {
            throw new \RuntimeException($live['error']);
        }

        return $live;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
