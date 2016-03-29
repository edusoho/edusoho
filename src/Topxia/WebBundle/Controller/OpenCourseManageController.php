<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OpenCourseManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $openCourse = $this->getOpenCourseService()->getCourse($id);

        return $this->forward('TopxiaWebBundle:OpenCourseManage:base', array('id' => $id));
    }

    public function baseAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $course = $this->getOpenCourseService()->getCourse($id);

        $courseSetting = $this->getSettingService()->get('course', array());

        /*if ($request->getMethod() == 'POST') {
        $data = $request->request->all();
        $this->getCourseService()->updateCourse($id, $data);
        $this->setFlashMessage('success', '课程基本信息已保存！');
        return $this->redirect($this->generateUrl('course_manage_base', array('id' => $id)));
        }*/

        $tags    = $this->getTagService()->findTagsByIds($course['tags']);
        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-base.html.twig', array(
            'course'  => $course,
            'tags'    => ArrayToolkit::column($tags, 'name'),
            'default' => $default
        ));
    }

    public function pictureAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $course = $this->getOpenCourseService()->getCourse($id);

        return $this->render('TopxiaWebBundle:CourseManage:picture.html.twig', array(
            'course' => $course
        ));
    }

    public function pictureCropAction(Request $request, $id)
    {
        $course         = $this->getCourseService()->tryManageOpenCourse($id);
        $openLiveLesson = $this->getCourseService()->getCourseLessons($course['id']);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $this->getCourseService()->changeCoursePicture($course['id'], $data["images"]);
            return $this->redirect($this->generateUrl('course_manage_picture', array('id' => $course['id'])));
        }

        $fileId                                      = $request->getSession()->get("fileId");
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render('TopxiaWebBundle:CourseManage:picture-crop.html.twig', array(
            'course'      => $course,
            'pictureUrl'  => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize'  => $scaledSize
        ));
    }

    public function teachersAction(Request $request, $id)
    {
        //$course = $this->getCourseService()->tryManageCourse($id);
        $course = $this->getOpenCourseService()->getCourse($id);

        if ($request->getMethod() == 'POST') {
            $data        = $request->request->all();
            $data['ids'] = empty($data['ids']) ? array() : array_values($data['ids']);

            $teachers = array();

            foreach ($data['ids'] as $teacherId) {
                $teachers[] = array(
                    'id'        => $teacherId,
                    'isVisible' => empty($data['visible_'.$teacherId]) ? 0 : 1
                );
            }

            $this->getCourseService()->setCourseTeachers($id, $teachers);

            $this->setFlashMessage('success', '教师设置成功！');

            return $this->redirect($this->generateUrl('open_course_manage_teachers', array('id' => $id)));
        }

        $teacherMembers = $this->getOpenCourseService()->searchMembers(array(
            'courseId'  => $id,
            'role'      => 'teacher',
            'isVisible' => 1
        ),
            array('seq', 'ASC'),
            0, 100
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($teacherMembers, 'userId'));

        $teachers = array();

        foreach ($teacherMembers as $member) {
            if (empty($users[$member['userId']])) {
                continue;
            }

            $teachers[] = array(
                'id'        => $member['userId'],
                'nickname'  => $users[$member['userId']]['nickname'],
                'avatar'    => $this->getWebExtension()->getFilePath($users[$member['userId']]['smallAvatar'], 'avatar.png'),
                'isVisible' => $member['isVisible'] ? true : false
            );
        }

        return $this->render('TopxiaWebBundle:CourseManage:teachers.html.twig', array(
            'course'   => $course,
            'teachers' => $teachers
        ));
    }

    public function liveOpenTimeSetAction(Request $request, $id)
    {
        //$liveCourse     = $this->getCourseService()->tryManageCourse($id);
        $liveCourse     = $this->getOpenCourseService()->getCourse($id);
        $openLiveLesson = $this->getOpenCourseService()->searchLessons(array('courseId' => $liveCourse['id']), array('startTime', 'DESC'), 0, 1);
        $openLiveLesson = $openLiveLesson ? $openLiveLesson[0] : array();

        if ($request->getMethod() == 'POST') {
            $liveLesson = $request->request->all();

            if ($openLiveLesson) {
                $updateLiveLesson['startTime'] = strtotime($liveLesson['startTime']);
                $updateLiveLesson['length']    = $liveLesson['timeLength'];

                $openLiveLesson = $this->getOpenCourseService()->updateLesson($liveCourse['id'], $openLiveLesson['id'], $updateLiveLesson);
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
                $liveLesson                 = $this->getOpenCourseService()->createLesson($liveLesson);
            }
        }

        return $this->render('TopxiaWebBundle:OpenCourseManage:live-open-time-set.html.twig', array(
            'course'         => $liveCourse,
            'openLiveLesson' => $openLiveLesson
        ));
    }

    public function marketingAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageOpenCourse($id);

        $userIds   = array();
        $coinPrice = 0;
        $price     = 0;

        if ($request->getMethod() == 'POST') {
            $courseIds = $request->request->get('courseIds');

            if (empty($courseIds)) {
                $courseIds = array();
            }

            $this->getOpenCourseRecommendedService()->updateOpenCourseRecommendedCourses($id, $courseIds);

            $this->setFlashMessage('success', "推荐课程修改成功");

            return $this->redirect($this->generateUrl('course_manage_open_marketing', array(
                'id' => $id
            )));
        }

        $recommends = $this->getOpenCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($id);

        $recommendedCourses = array();

        foreach ($recommends as $key => $existCourse) {
            $recommendedCourses[] = $this->getCourseService()->getCourse($existCourse['recommendCourseId']);
        }

        foreach ($recommendedCourses as $recommendedCourse) {
            $userIds = array_merge($userIds, $course['teacherIds']);

            $coinPrice += $recommendedCourse['coinPrice'];
            $price += $recommendedCourse['price'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-marketing.html.twig', array(
            'courses'   => $recommendedCourses,
            'price'     => $price,
            'coinPrice' => $coinPrice,
            'users'     => $users,
            'course'    => $course
        ));
    }

    public function pickAction(Request $request, $filter, $id)
    {
        $user                   = $this->getCurrentUser();
        $course                 = $this->getCourseService()->tryManageOpenCourse($id);
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
            'courseId'  => $id,
            'filter'    => $filter
        ));
    }

    public function searchAction(Request $request, $id, $filter)
    {
        $user = $this->getCurrentUser();
        $this->getCourseService()->tryManageOpenCourse($id);
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

    public function recommendesCoursesSelectAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageOpenCourse($id);

        $data = $request->request->all();
        $ids  = array();

        if (isset($data['ids']) && $data['ids'] != "") {
            $ids = $data['ids'];
            $ids = explode(",", $ids);
        } else {
            return new Response('success');
        }

        $this->getOpenCourseRecommendedService()->addRecommendedCoursesToOpenCourse($id, $ids);
        $this->setFlashMessage('success', "推荐课程添加成功");

        return new Response('success');
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

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getOpenCourseRecommendedService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseRecommendedService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
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
