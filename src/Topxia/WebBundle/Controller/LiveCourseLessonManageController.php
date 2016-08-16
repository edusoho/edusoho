<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class LiveCourseLessonManageController extends BaseController
{
    public function createAction(Request $request, $id)
    {
        $liveCourse = $this->getCourseService()->tryManageCourse($id);
        $parentId   = $request->query->get('parentId');

        if ($request->getMethod() == 'POST') {
            $liveLesson = $request->request->all();

            // 直播课时名称只支持中文、英文大小写以及数字，暂不支持<、>、"、&、‘、’、”、“字符
            // if (!(bool) preg_match('/^[^(<|>|\'|"|&|‘|’|”|“)]*$/', $liveLesson['title'])) {
            //    throw $this->createAccessDeniedException("illegal title");
            // }

            $liveLesson['type']      = 'live';
            $liveLesson['courseId']  = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLesson['startTime']);
            $liveLesson['length']    = $liveLesson['timeLength'];

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
                'summary'     => $liveLesson['summary'],
                'title'       => $liveLesson['title'],
                'speaker'     => $speaker,
                'startTime'   => $liveLesson['startTime'].'',
                'endTime'     => ($liveLesson['startTime'] + $liveLesson['length'] * 60).'',
                'authUrl'     => $this->generateUrl('live_auth', array(), true),
                'jumpUrl'     => $this->generateUrl('live_jump', array('id' => $liveLesson['courseId']), true),
                'liveLogoUrl' => $liveLogoUrl
            ));

            if (empty($live)) {
                throw new \RuntimeException('创建直播教室失败，请重试！');
            }

            if (isset($live['error'])) {
                throw new \RuntimeException($live['error']);
            }

            $liveLesson['mediaId']      = $live['id'];
            $liveLesson['liveProvider'] = $live['provider'];
            $liveLesson                 = $this->getCourseService()->createLesson($liveLesson);

            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $liveCourse,
                'lesson' => $liveLesson
            ));
        }

        return $this->render('TopxiaWebBundle:LiveCourseLessonManage:live-lesson-modal.html.twig', array(
            'liveCourse' => $liveCourse,
            'parentId'   => $parentId
        ));
    }

    public function editAction(Request $request, $courseId, $lessonId)
    {
        $liveCourse = $this->getCourseService()->tryManageCourse($courseId);
        $liveLesson = $this->getCourseService()->getCourseLesson($liveCourse['id'], $lessonId);

        if ($request->getMethod() == 'POST') {
            $editLiveLesson = $request->request->all();

            $liveLesson['type']      = 'live';
            $liveLesson['title']     = $editLiveLesson['title'];
            $liveLesson['summary']   = $editLiveLesson['summary'];
            $liveLesson['courseId']  = $liveCourse['id'];
            $liveLesson['startTime'] = empty($editLiveLesson['startTime']) ? $liveLesson['startTime'] : strtotime($editLiveLesson['startTime']);
            $liveLesson['free']      = empty($editLiveLesson['free']) ? 0 : $editLiveLesson['free'];
            $liveLesson['length']    = empty($editLiveLesson['timeLength']) ? $liveLesson['length'] : $editLiveLesson['timeLength'];

            $speakerId = current($liveCourse['teacherIds']);
            $speaker   = $speakerId ? $this->getUserService()->getUser($speakerId) : null;
            $speaker   = $speaker ? $speaker['nickname'] : '老师';

            $liveParams = array(
                'liveId'   => $liveLesson['mediaId'],
                'provider' => $liveLesson['liveProvider'],
                'summary'  => $editLiveLesson['summary'],
                'title'    => $editLiveLesson['title'],
                'speaker'  => $speaker,
                'authUrl'  => $this->generateUrl('live_auth', array(), true),
                'jumpUrl'  => $this->generateUrl('live_jump', array('id' => $liveLesson['courseId']), true)
            );

            if (array_key_exists('startTime', $liveLesson)) {
                $liveParams['startTime'] = $liveLesson['startTime'];
            }

            if (array_key_exists('startTime', $liveLesson) && array_key_exists('length', $liveLesson)) {
                $liveParams['endTime'] = ($liveLesson['startTime'] + $liveLesson['length'] * 60).'';
            }

            $client = new EdusohoLiveClient();
            $live   = $client->updateLive($liveParams);

            $liveLesson = $this->getCourseService()->updateLesson($courseId, $lessonId, $liveLesson);

            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $liveCourse,
                'lesson' => $liveLesson
            ));
        }

        return $this->render('TopxiaWebBundle:LiveCourseLessonManage:live-lesson-modal.html.twig', array(
            'liveCourse' => $liveCourse,
            'liveLesson' => $liveLesson
        ));
    }

    public function lessonTimeCheckAction(Request $request, $id)
    {
        $data = $request->query->all();

        $startTime = $data['startTime'];
        $length    = $data['length'];
        $lessonId  = empty($data['lessonId']) ? "" : $data['lessonId'];

        list($result, $message) = $this->getCourseService()->liveLessonTimeCheck($id, $lessonId, $startTime, $length);

        if ($result == 'success') {
            $response = array('success' => true, 'message' => '这个时间段的课时可以创建');
        } else {
            $response = array('success' => false, 'message' => $message);
        }

        return $this->createJsonResponse($response);
    }

    public function calculateLeftCapacityAction(Request $request, $courseId)
    {
        $data = $request->query->all();

        $startTime = strtotime($data['startTime']);
        $length    = $data['length'];
        $endTime   = $startTime + $length * 60;
        $lessonId  = empty($data['lessonId']) ? "" : $data['lessonId'];

        $leftCapacity = $this->getCourseService()->calculateLiveCourseLeftCapacityInTimeRange($startTime, $endTime, $lessonId);

        return $this->createJsonResponse($leftCapacity);
    }

    public function editLessonReplayAction(Request $request, $lessonId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        if ($request->getMethod() == 'POST') {
            $ids = $request->request->get("visibleReplaies");
            $this->getCourseService()->updateCourseLessonReplayByLessonId($lessonId, array('hidden' => 1));

            foreach ($ids as $id) {
                $this->getCourseService()->updateCourseLessonReplay($id, array('hidden' => 0));
            }

            return $this->redirect($this->generateUrl('live_course_manage_replay', array('id' => $courseId)));
        }

        $replayLessons = $this->getCourseService()->getCourseLessonReplayByLessonId($lessonId);
        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:replay-lesson-modal.html.twig', array(
            'replayLessons' => $replayLessons,
            'lessonId'      => $lessonId,
            'courseId'      => $courseId,
            'lesson'        => $lesson
        ));
    }

    public function updateLessonReplayAction(Request $request, $courseId, $lessonId, $replayId)
    {
        $title = $request->request->get('title');

        if (empty($title)) {
            return $this->createJsonResponse(false);
        }

        $course = $this->getCourseService()->tryManageCourse($courseId);
        $this->getCourseService()->updateCourseLessonReplay($replayId, array('title' => $title));
        return $this->createJsonResponse(true);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
