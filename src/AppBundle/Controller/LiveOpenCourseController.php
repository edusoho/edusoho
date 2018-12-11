<?php

namespace AppBundle\Controller;

use Biz\Util\EdusohoLiveClient;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\File\Service\UploadFileService;
use Biz\OpenCourse\Service\OpenCourseService;
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

        $params['role'] = $this->getLiveCourseService()->checkCourseUserRole($course, $lesson);

        $user = $this->getCurrentUser();
        $params['id'] = $user->isLogin() ? $user['id'] : $this->getRandomUserId($request, $courseId, $lessonId);
        $params['nickname'] = $user->isLogin() ? $user['nickname'] : $this->getRandomNickname($request, $courseId, $lessonId);
        $params['isLogin'] = $user->isLogin();
        $params['startTime'] = $lesson['startTime'];
        $params['endTime'] = $lesson['endTime'];
        $this->createRefererLog($request, $course);

        return $this->forward('AppBundle:Liveroom:_entry',
        array(
            'roomId' => $lesson['mediaId'],
        ), $params);
    }

    protected function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());

        return (float) sprintf('%.0f', ((float) ($t1) + (float) ($t2)) * 1000);
    }

    protected function getRandomNickname(Request $request, $courseId, $lessonId)
    {
        $key = "live-open-course-nickname-{$courseId}-{$lessonId}";
        $sessionValue = $request->getSession()->get($key);
        if (empty($sessionValue)) {
            $sessionValue = '游客'.$this->getRandomString(8);
            $request->getSession()->set($key, $sessionValue);
        }

        return $sessionValue;
    }

    protected function getRandomUserId(Request $request, $courseId, $lessonId)
    {
        $key = "live-open-course-user-id-{$courseId}-{$lessonId}";
        $sessionValue = $request->getSession()->get($key);
        if (empty($sessionValue)) {
            $sessionValue = (int) ($this->getMillisecond()) * 1000 + rand(0, 999);
            $request->getSession()->set($key, $sessionValue);
        }

        return $sessionValue;
    }

    protected function getRandomString($length, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $s = '';
        $cLength = strlen($chars);

        while (strlen($s) < $length) {
            $s .= $chars[mt_rand(0, $cLength - 1)];
        }

        return $s;
    }

    public function verifyAction(Request $request)
    {
        $result = array(
            'code' => '0',
            'msg' => 'ok',
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

        $resultList = $this->getLiveReplayService()->generateReplay($lesson['mediaId'], $course['id'], $lesson['id'], $lesson['liveProvider'], $course['type']);

        if (isset($resultList['error']) && !empty($resultList['error'])) {
            return $this->createJsonResponse($resultList);
        }

        $client = new EdusohoLiveClient();
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);
        $lesson['isEnd'] = (int) (time() - $lesson['endTime']) > 0;
        $lesson['canRecord'] = $client->isAvailableRecord($lesson['mediaId']);

        return $this->render('live-course-replay-manage/list-item.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
        ));
    }

    public function editLessonReplayAction(Request $request, $lessonId, $courseId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        if (!$lesson) {
            return $this->createMessageResponse('error', '改课程不存在或已删除！');
        }

        if ('POST' == $request->getMethod()) {
            $ids = $request->request->get('visibleReplaies');
            $this->getLiveReplayService()->updateReplayByLessonId($lessonId, array('hidden' => 1), 'liveOpen');

            foreach ($ids as $id) {
                $this->getLiveReplayService()->updateReplay($id, array('hidden' => 0));
            }

            return $this->redirect($this->generateUrl('live_open_course_manage_replay', array('id' => $courseId)));
        }

        $replayLessons = $this->getLiveReplayService()->searchReplays(
            array(
                'lessonId' => $lessonId,
                'type' => 'liveOpen',
            ),
            array('replayId' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return $this->render('live-course-replay-manage/replay-lesson-modal.html.twig', array(
            'replayLessons' => $replayLessons,
            'lessonId' => $lessonId,
            'courseId' => $courseId,
            'lesson' => $lesson,
        ));
    }

    public function updateReplayTitleAction(Request $request, $courseId, $lessonId, $replayId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);

        $title = $request->request->get('title');

        if (empty($title)) {
            return $this->createJsonResponse(false);
        }

        $this->getLiveReplayService()->updateReplay($replayId, array('title' => $title));

        return $this->createJsonResponse(true);
    }

    public function replayManageAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course['id']);

        $client = new EdusohoLiveClient();
        foreach ($lessons as $key => $lesson) {
            $lesson['isEnd'] = $this->get('web.twig.live_extension')->isLiveFinished($lesson['id'], 'openCourse');
            $lesson['canRecord'] = !('videoGenerated' == $lesson['replayStatus']) && $client->isAvailableRecord($lesson['mediaId']);
            $lesson['file'] = $this->getLiveReplayMedia($lesson);
            $lessons["lesson-{$lesson['id']}"] = $lesson;
        }

        $default = $this->getSettingService()->get('default', array());

        return $this->render('live-course-replay-manage/index.html.twig', array(
            'course' => $course,
            'items' => $lessons,
            'default' => $default,
        ));
    }

    public function entryReplayAction(Request $request, $courseId, $lessonId, $replayId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);
        $this->createRefererLog($request, $course);

        return $this->render('live-course/classroom.html.twig', array(
            'lesson' => $lesson,
            'url' => $this->generateUrl('live_open_course_live_replay_url', array(
                'courseId' => $courseId,
                'lessonId' => $lessonId,
                'replayId' => $replayId,
            )),
        ));
    }

    public function getReplayUrlAction(Request $request, $courseId, $lessonId, $replayId)
    {
        $ssl = $request->isSecure() ? true : false;

        $course = $this->getOpenCourseService()->getCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($course['id'], $lessonId);

        $result = $this->getLiveReplayService()->entryReplay($replayId, $lesson['mediaId'], $lesson['liveProvider'], $ssl);

        if (!empty($result) && !empty($result['resourceNo'])) {
            $result['url'] = $this->generateUrl('es_live_room_replay_show', array(
                'replayId' => $replayId,
                'targetId' => $course['id'],
                'targetType' => LiveroomController::LIVE_OPEN_COURSE_TYPE,
                'lessonId' => $lesson['id'],
            ));
        }

        return $this->createJsonResponse(array(
            'url' => $result['url'],
            'param' => isset($result['param']) ? $result['param'] : null,
        ));
    }

    public function uploadModalAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        if ('videoGenerated' == $lesson['replayStatus']) {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!empty($file)) {
                $lesson['media'] = $file;
            } else {
                $lesson['media'] = array('id' => 0, 'convertStatus' => 'none', 'source' => '', 'filename' => '文件已删除', 'uri' => '', 'length' => 0, 'fileSize' => 0);
            }
        }

        if ('POST' == $request->getMethod()) {
            $fileId = $request->request->get('fileId', 0);
            $this->getOpenCourseService()->generateLessonVideoReplay($courseId, $lessonId, $fileId);

            return $this->redirect(
                $this->generateUrl(
                    'live_open_course_manage_replay',
                    array(
                        'id' => $courseId,
                    )
                )
            );
        }

        return $this->render('live-course-replay-manage/upload-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'targetType' => 'opencourselesson',
        ));
    }

    protected function getLiveReplayMedia($lesson)
    {
        if ('liveOpen' == $lesson['type'] && 'videoGenerated' == $lesson['replayStatus']) {
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

            if ($start && 1 == $treeCategory['depth']) {
                return $treeCategory;
            }
        }

        return null;
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getLiveCourseService()
    {
        return $this->getBiz()->service('OpenCourse:LiveCourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    protected function getLiveReplayService()
    {
        return $this->getBiz()->service('Course:LiveReplayService');
    }
}
