<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class LiveOpenCourseController extends BaseOpenCourseController
{
    public function entryAction(Request $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $result = $this->getLiveCourseService()->checkLessonStatus($lesson);

        if (!$result['result']) {
            return $this->createMessageResponse('info', $result['message']);
        }

        $params = array();

        $params['role'] = $this->getLiveCourseService()->checkCourseUserRole($lesson);

        $user               = $this->getCurrentUser();
        $params['id']       = $user->isLogin() ? $user['id'] : $this->getRandomUserId($request, $courseId, $lessonId);
        $params['nickname'] = $user->isLogin() ? $user['nickname'] : $this->getRandomNickname($request, $courseId, $lessonId);
        $this->createRefererLog($request, $course);
        return $this->forward('TopxiaWebBundle:Liveroom:_entry', array('id' => $lesson['mediaId']), $params);
    }

    protected function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

    protected function getRandomNickname($request, $courseId, $lessonId)
    {
        $key          = "live-open-course-nickname-{$courseId}-{$lessonId}";
        $sessionValue = $request->getSession()->get($key);
        if (empty($sessionValue)) {
            $sessionValue = '游客'.$this->getRandomString(8);
            $request->getSession()->set($key, $sessionValue);
        }
        return $sessionValue;
    }

    protected function getRandomUserId($request, $courseId, $lessonId)
    {
        $key          = "live-open-course-user-id-{$courseId}-{$lessonId}";
        $sessionValue = $request->getSession()->get($key);
        if (empty($sessionValue)) {
            $sessionValue = $this->getMillisecond();
            $request->getSession()->set($key, $sessionValue);
        }
        return $sessionValue;
    }

    protected function getRandomString($length, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $s       = '';
        $cLength = strlen($chars);

        while (strlen($s) < $length) {
            $s .= $chars[mt_rand(0, $cLength - 1)];
        }

        return $s;
    }

    public function verifyAction(Request $request)
    {
        $result = array(
            "code" => "0",
            "msg"  => "ok"
        );

        return $this->createJsonResponse($result);
    }

    protected function makeSign($string)
    {
        $secret = $this->container->getParameter('secret');
        return md5($string.$secret);
    }

    public function createLessonReplayAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if (!$lesson) {
            return $this->createMessageResponse('error', '改课程不存在或已删除！');
        }

        $resultList = $this->getLiveCourseService()->generateLessonReplay($course, $lesson);

        if (isset($resultList['error']) && !empty($resultList['error'])) {
            return $this->createJsonResponse($resultList);
        }

        $client              = new EdusohoLiveClient();
        $lesson              = $this->getOpenCourseService()->getLesson($lessonId);
        $lesson["isEnd"]     = intval(time() - $lesson["endTime"]) > 0;
        $lesson['canRecord'] = $client->isAvailableRecord($lesson['mediaId']);

        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:list-item.html.twig', array(
            'course' => $course,
            'lesson' => $lesson
        ));
    }

    public function editLessonReplayAction(Request $request, $lessonId, $courseId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        if (!$lesson) {
            return $this->createMessageResponse('error', '改课程不存在或已删除！');
        }

        if ($request->getMethod() == 'POST') {
            $ids = $request->request->get("visibleReplaies");
            $this->getCourseService()->updateCourseLessonReplayByLessonId($lessonId, array('hidden' => 1), 'liveOpen');

            foreach ($ids as $id) {
                $this->getCourseService()->updateCourseLessonReplay($id, array('hidden' => 0));
            }

            return $this->redirect($this->generateUrl('live_open_course_manage_replay', array('id' => $courseId)));
        }

        $replayLessons = $this->getCourseService()->searchCourseLessonReplays(array('lessonId' => $lessonId, 'type' => 'liveOpen'), array('replayId', 'ASC'), 0, PHP_INT_MAX);

        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:replay-lesson-modal.html.twig', array(
            'replayLessons' => $replayLessons,
            'lessonId'      => $lessonId,
            'courseId'      => $courseId,
            'lesson'        => $lesson
        ));
    }

    public function updateReplayTitleAction(Request $request, $courseId, $lessonId, $replayId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);

        $title = $request->request->get('title');

        if (empty($title)) {
            return $this->createJsonResponse(false);
        }

        $this->getCourseService()->updateCourseLessonReplay($replayId, array('title' => $title));
        return $this->createJsonResponse(true);
    }

    public function replayManageAction(Request $request, $id)
    {
        $course  = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course['id']);

        $client = new EdusohoLiveClient();
        foreach ($lessons as $key => $lesson) {
            $lesson["isEnd"]                   = intval(time() - $lesson["endTime"]) > 0;
            $lesson["canRecord"]               = !($lesson['replayStatus'] == 'videoGenerated') && $client->isAvailableRecord($lesson['mediaId']);
            $lesson['file']                    = $this->getLiveReplayMedia($lesson);
            $lessons["lesson-{$lesson['id']}"] = $lesson;
        }

        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:index.html.twig', array(
            'course'  => $course,
            'items'   => $lessons,
            'default' => $default
        ));
    }

    public function entryReplayAction(Request $request, $courseId, $lessonId, $replayId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);
        $this->createRefererLog($request, $course);
        return $this->render("TopxiaWebBundle:LiveCourse:classroom.html.twig", array(
            'lesson' => $lesson,
            'url'    => $this->generateUrl('live_open_course_live_replay_url', array(
                'courseId' => $courseId,
                'lessonId' => $lessonId,
                'replayId' => $replayId
            ))
        ));
    }

    public function getReplayUrlAction(Request $request, $courseId, $lessonId, $replayId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $result = $this->getLiveCourseService()->entryReplay($replayId);

        return $this->createJsonResponse(array(
            'url'   => $result['url'],
            'param' => isset($result['param']) ? $result['param'] : null
        ));
    }

    public function uploadModalAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        $file = array();
        if ($lesson['replayStatus'] == 'videoGenerated') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!empty($file)) {
                $lesson['media'] = array(
                    'id'     => $file['id'],
                    'status' => $file['convertStatus'],
                    'source' => 'self',
                    'name'   => $file['filename'],
                    'uri'    => ''
                );
            } else {
                $lesson['media'] = array('id' => 0, 'status' => 'none', 'source' => '', 'name' => '文件已删除', 'uri' => '');
            }
        }

        if ($request->getMethod() == 'POST') {
            $fileId = $request->request->get('fileId', 0);
            $this->getOpenCourseService()->generateLessonVideoReplay($courseId, $lessonId, $fileId);
        }

        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:upload-modal.html.twig', array(
            'course'     => $course,
            'lesson'     => $lesson,
            'targetType' => 'opencourselesson'
        ));
    }

    protected function getLiveReplayMedia($lesson)
    {
        if ($lesson['type'] == 'liveOpen' && $lesson['replayStatus'] == 'videoGenerated') {
            return $this->getUploadFileService()->getFile($lesson['mediaId']);
        }

        return '';
    }

    protected function getRootCategory($categoryTree, $category)
    {
        $start = false;

        foreach (array_reverse($categoryTree) as $treeCategory) {
            if ($treeCategory['id'] == $category['id']) {
                $start = true;
            }

            if ($start && $treeCategory['depth'] == 1) {
                return $treeCategory;
            }
        }

        return null;
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getLiveCourseService()
    {
        return $this->getServiceKernel()->createService('Course.LiveCourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}
