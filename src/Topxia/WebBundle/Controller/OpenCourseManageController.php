<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseManageController extends BaseController
{
    public function liveOpenTimeSetAction(Request $request, $id)
    {
        $course         = $this->getCourseService()->tryManageCourse($id);
        $openLiveLesson = $this->getCourseService()->getCourseLessons($course['id']);

        if ($request->getMethod() == 'POST') {
            $liveLesson              = $request->request->all();
            $liveLesson['type']      = 'liveOpen';
            $liveLesson['courseId']  = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLesson['startTime']);
            $liveLesson['length']    = $liveLesson['timeLength'];
            $liveLesson['title']     = $course['title'];

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
        }

        return $this->render('TopxiaWebBundle:OpenCourseManage:live-open-time-set.html.twig', array(
            'course'         => $course,
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

    public function pickAction(Request $request, $filter, $id)
    {
        $user                   = $this->getCurrentUser();
        $course                 = $this->getCourseService()->tryManageCourse($id);
        $conditions             = $request->query->all();
        $conditions['status']   = 'published';
        $conditions['parentId'] = 0;

        if ($filter == 'openCourse') {
            $conditions['type']   = 'open';
            $conditions['userId'] = $user['id'];
        }

        if ($filter == 'otherCourse') {
            $conditions['type']   = 'normal';
            $conditions['userId'] = $user['id'];
        }

        if ($filter == 'normal') {
        }

        if (isset($conditions['title']) && $conditions['title'] == "") {
            unset($conditions['title']);
        }

        $count     = $this->getCourseService()->searchCourseCount($conditions);
        $paginator = new Paginator($this->get('request'), $count, 5);
        $courses   = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $courseIds = ArrayToolkit::column($courses, 'id');
        $userIds   = array();

        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds        = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-pick-modal.html.twig', array(
            'users'     => $users,
            'courses'   => $courses,
            'paginator' => $paginator,
            'courseId'  => $course['id'],
            'filter'    => $filter
        ));
    }

    public function searchAction(Request $request, $id, $filter)
    {
        $user = $this->getCurrentUser();
        $this->getCourseService()->tryManageCourse($id);
        $key = $request->request->get("key");

        if (isset($key) && $key == "") {
            unset($key);
        }

        $conditions = array("title" => $key);

        $conditions['status']   = 'published';
        $conditions['parentId'] = 0;

        if ($filter == 'openCourse') {
            $conditions['type']   = 'open';
            $conditions['userId'] = $user['id'];
        }

        if ($filter == 'otherCourse') {
            $conditions['type']   = 'normal';
            $conditions['userId'] = $user['id'];
        }

        if ($filter == 'normal') {
        }

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            0,
            5
        );

        $courseIds = ArrayToolkit::column($courses, 'id');

        $userIds = array();

        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds        = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:Course:course-select-list.html.twig', array(
            'users'   => $users,
            'courses' => $courses
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }
}
