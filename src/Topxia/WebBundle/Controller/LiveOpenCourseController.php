<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class LiveOpenCourseController extends BaseController
{
    public function entryAction(Request $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        $result = $this->getLiveCourseService()->checkLessonStatus($lesson);

        if (!$result['result']) {
            return $this->createMessageResponse('info', $result['message']);
        }

        $params = array();

        $params['role'] = $this->getLiveCourseService()->checkCourseUserRole($lesson);

        $liveAccount = CloudAPIFactory::create('leaf')->get('/me/liveaccount');

        $user               = $this->getCurrentUser();
        $params['id']       = $user->isLogin() ? $user['id'] : 0;
        $params['nickname'] = $user->isLogin() ? $user['nickname'] : 'guest';

        return $this->forward('TopxiaWebBundle:Liveroom:_entry', array('id' => $lesson['mediaId']), $params);
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
        //$course = $this->getCourseService()->tryManageCourse($courseId);
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if (!$lesson) {
            return $this->createMessageResponse('error', '改课程不存在或已删除！');
        }

        $resultList = $this->getLiveCourseService()->generateLessonReplay($course, $lesson);

        if (isset($resultList['error']) && !empty($resultList['error'])) {
            return $this->createJsonResponse($resultList);
        }

        $lesson["isEnd"] = intval(time() - $lesson["endTime"]) > 0;

        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:list-item.html.twig', array(
            'course' => $course,
            'lesson' => $lesson
        ));
    }

    public function editLessonReplayAction(Request $request, $lessonId, $courseId)
    {
        //$course = $this->getCourseService()->tryManageCourse($courseId);
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

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
            'courseId'      => $courseId
        ));
    }

    public function entryReplayAction(Request $request, $courseId, $lessonId, $courseLessonReplayId)
    {
        $course = $this->getCourseService()->tryTakeCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        return $this->render("TopxiaWebBundle:LiveCourse:classroom.html.twig", array(
            'lesson' => $lesson,
            'url'    => $this->generateUrl('live_classroom_replay_url', array(
                'courseId'             => $courseId,
                'lessonId'             => $lessonId,
                'courseLessonReplayId' => $courseLessonReplayId
            ))
        ));
    }

    public function getReplayUrlAction(Request $request, $courseId, $lessonId, $courseLessonReplayId)
    {
        $course = $this->getCourseService()->tryTakeCourse($courseId);
        $result = $this->getCourseService()->entryReplay($lessonId, $courseLessonReplayId);

        return $this->createJsonResponse(array(
            'url'   => $result['url'],
            'param' => isset($result['param']) ? $result['param'] : null
        ));
    }

    public function replayManageAction(Request $request, $id)
    {
        //$course      = $this->getCourseService()->tryManageCourse($id);
        $course  = $this->getOpenCourseService()->getCourse($id);
        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course['id']);

        foreach ($lessons as $key => $lesson) {
            $lesson["isEnd"]                   = intval(time() - $lesson["endTime"]) > 0;
            $lessons["lesson-{$lesson['id']}"] = $lesson;
        }

        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:index.html.twig', array(
            'course'  => $course,
            'items'   => $lessons,
            'default' => $default
        ));
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
}
