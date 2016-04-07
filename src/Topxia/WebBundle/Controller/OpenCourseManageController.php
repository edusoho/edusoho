<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OpenCourseManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $openCourse = $this->getOpenCourseService()->tryManageOpenCourse($id);

        return $this->forward('TopxiaWebBundle:OpenCourseManage:base', array('id' => $id));
    }

    public function baseAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $courseSetting = $this->getSettingService()->get('course', array());

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            $this->getOpenCourseService()->updateCourse($id, $data);
            $this->setFlashMessage('success', '课程基本信息已保存！');
            return $this->redirect($this->generateUrl('open_course_manage_base', array('id' => $id)));
        }

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
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        return $this->render('TopxiaWebBundle:CourseManage:picture.html.twig', array(
            'course' => $course

        ));
    }

    public function pictureCropAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $this->getOpenCourseService()->changeCoursePicture($course['id'], $data["images"]);
            return $this->redirect($this->generateUrl('open_course_manage_picture', array('id' => $course['id'])));
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
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

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

            $this->getOpenCourseService()->setCourseTeachers($id, $teachers);

            $this->setFlashMessage('success', '教师设置成功！');

            return $this->redirect($this->generateUrl('open_course_manage_teachers', array('id' => $id)));
        }

        $teacherMembers = $this->getOpenCourseService()->searchMembers(
            array(
                'courseId'  => $id,
                'role'      => 'teacher',
                'isVisible' => 1
            ),
            array('seq', 'ASC'),
            0,
            100
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

    public function studentsAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $fields = $request->query->all();

        $condition = array('courseId' => $course['id'], 'role' => 'student');

        if (isset($fields['userType']) && $fields['userType'] == 'login') {
            $condition['userIdGT'] = 0;
        }

        if (isset($fields['userType']) && $fields['userType'] == 'unlogin') {
            $condition['userId'] = 0;
        }

        $paginator = new Paginator(
            $request,
            $this->getOpenCourseService()->searchMemberCount($condition),
            20
        );

        $students = $this->getOpenCourseService()->searchMembers(
            $condition,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users          = $this->getUserService()->findUsersByIds($studentUserIds);

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-students.html.twig', array(
            'course'    => $course,
            'students'  => $students,
            'users'     => $users,
            'paginator' => $paginator
        ));
    }

    public function liveOpenTimeSetAction(Request $request, $id)
    {
        $liveCourse = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $openLiveLesson = $this->getOpenCourseService()->searchLessons(array('courseId' => $liveCourse['id']), array('startTime', 'DESC'), 0, 1);
        $liveLesson     = $openLiveLesson ? $openLiveLesson[0] : array();

        if ($request->getMethod() == 'POST') {
            $liveLessonFields = $request->request->all();

            $liveLesson['type']      = 'liveOpen';
            $liveLesson['courseId']  = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLessonFields['startTime']);
            $liveLesson['length']    = $liveLessonFields['timeLength'];
            $liveLesson['title']     = $liveCourse['title'];
            $liveLesson['status']    = 'published';

            if ($openLiveLesson) {
                $live       = $this->getLiveCourseService()->editLiveRoom($liveCourse, $liveLesson, $this->container);
                $liveLesson = $this->getOpenCourseService()->updateLesson($liveLesson['courseId'], $liveLesson['id'], $liveLesson);
            } else {
                $live = $this->getLiveCourseService()->createLiveRoom($liveCourse, $liveLesson, $this->container);

                $liveLesson['mediaId']      = $live['id'];
                $liveLesson['liveProvider'] = $live['provider'];

                $liveLesson = $this->getOpenCourseService()->createLesson($liveLesson);
            }
        }

        return $this->render('TopxiaWebBundle:OpenCourseManage:live-open-time-set.html.twig', array(
            'course'         => $liveCourse,
            'openLiveLesson' => $liveLesson
        ));
    }

    public function marketingAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

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

            return $this->redirect($this->generateUrl('open_course_manage_marketing', array(
                'id' => $id
            )));
        }

        $recommends = $this->getOpenCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($id);

        $recommendedCourses = array();

        foreach ($recommends as $key => $recommend) {
            $recommendedCourses[] = $this->getRecommendCourseData($recommend['recommendCourseId'], $recommend['origin']);
        }

        foreach ($recommendedCourses as $recommendedCourse) {
            $userIds = array_merge($userIds, $course['teacherIds']);

            if ($recommendedCourse['type'] == 'normal' || $recommendedCourse['type'] == 'live') {
                $coinPrice += $recommendedCourse['coinPrice'];
                $price += $recommendedCourse['price'];
            }
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
        $user                    = $this->getCurrentUser();
        $course                  = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $existRecommendCourseIds = $this->getExistRecommendCourseIds($id);
        $conditions              = $request->query->all();
        $conditions['status']    = 'published';
        $conditions['parentId']  = 0;

        if ($filter == 'openCourse') {
            $conditions['type']       = 'open';
            $conditions['userId']     = $user['id'];
            $conditions['excludeIds'] = $existRecommendCourseIds['openCourse'];
        }

        if ($filter == 'otherCourse') {
            $conditions['type']       = 'normal';
            $conditions['userId']     = $user['id'];
            $conditions['excludeIds'] = (empty($existRecommendCourseIds['course'])) ? null : $existRecommendCourseIds['course'];
        }

        if ($filter == 'normal') {
            $conditions['excludeIds'] = (empty($existRecommendCourseIds['course'])) ? null : $existRecommendCourseIds['course'];
        }

        if (isset($conditions['title']) && $conditions['title'] == "") {
            unset($conditions['title']);
        }

        $result    = $this->getPickCourseData($filter, $this->get('request'), $conditions);
        $paginator = $result['paginator'];
        $courses   = $result['courses'];

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
        $this->getOpenCourseService()->tryManageOpenCourse($id);
        $existRecommendCourseIds = $this->getExistRecommendCourseIds($id);
        $key                     = $request->request->get("key");

        if (isset($key) && $key == "") {
            unset($key);
        }

        $conditions = array("title" => $key);

        $conditions['status']   = 'published';
        $conditions['parentId'] = 0;

        if ($filter == 'openCourse') {
            $conditions['type']       = 'open';
            $conditions['userId']     = $user['id'];
            $conditions['excludeIds'] = $existRecommendCourseIds['openCourse'];
        }

        if ($filter == 'otherCourse') {
            $conditions['type']       = 'normal';
            $conditions['userId']     = $user['id'];
            $conditions['excludeIds'] = (empty($existRecommendCourseIds['course'])) ? null : $existRecommendCourseIds['course'];
        }

        if ($filter == 'normal') {
            $conditions['excludeIds'] = (empty($existRecommendCourseIds['course'])) ? null : $existRecommendCourseIds['course'];
        }

        $courses = $this->getSearchCourseData($filter, $conditions);

        $courseIds = ArrayToolkit::column($courses, 'id');

        $userIds = array();

        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds        = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:Course:course-select-list.html.twig', array(
            'users'   => $users,
            'courses' => $courses,
            'filter'  => $filter
        ));
    }

    public function recommendedCoursesSelectAction(Request $request, $id, $filter)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $types  = array(
            'openCourse'   => 'open_course',
            'other_course' => 'course',
            'normal'       => 'course'
        );

        $origin = $types[$filter];
        $data   = $request->request->all();
        $ids    = array();

        if (isset($data['ids']) && $data['ids'] != "") {
            $ids = $data['ids'];
            $ids = explode(",", $ids);
        } else {
            return new Response('success');
        }

        $this->getOpenCourseRecommendedService()->addRecommendedCoursesToOpenCourse($id, $ids, $origin);
        $this->setFlashMessage('success', "推荐课程添加成功");

        return new Response('success');
    }

    public function publishAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $result = $this->getOpenCourseService()->publishCourse($id);

        if ($course['type'] == 'liveOpen' && !$result['result']) {
            $result['message'] = '请先设置直播时间';
        }

        if ($course['type'] == 'open' && !$result['result']) {
            $result['message'] = '请先创建课时';
        }

        return $this->createJsonResponse($result);
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

    private function getOpenCourse($request, $conditions)
    {
        $paginator = new Paginator(
            $request,
            $this->getOpenCourseService()->searchCourseCount($conditions),
            5
        );

        $courses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('createdTime', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
    }

    private function getPickCourseData($type, $request, $conditions)
    {
        $types = array(
            'openCourse'  => 'OpenCourseService',
            'otherCourse' => 'CourseService',
            'normal'      => 'CourseService'
        );

        $method = 'get'.$types[$type];

        $paginator = new Paginator(
            $request,
            $this->$method()->searchCourseCount($conditions),
            5
        );

        $courses = $this->$method()->searchCourses(
            $conditions,
            array('createdTime', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $result              = array();
        $result['paginator'] = $paginator;
        $result['courses']   = $courses;

        return $result;
    }

    private function getSearchCourseData($type, $conditions)
    {
        $types = array(
            'openCourse'  => 'OpenCourseService',
            'otherCourse' => 'CourseService',
            'normal'      => 'CourseService'
        );

        $method = 'get'.$types[$type];

        $courses = $this->$method()->searchCourses(
            $conditions,
            array('createdTime', 'ASC'),
            0,
            5
        );

        return $courses;
    }

    private function getRecommendCourseData($courseId, $origin)
    {
        $types = array(
            'open_course' => 'OpenCourseService',
            'course'      => 'CourseService'
        );

        $method = 'get'.$types[$origin];

        $course = $this->$method()->getCourse($courseId);
        return $course;
    }

    private function getExistRecommendCourseIds($openCourseId)
    {
        $existRecommendCourses = $this->getOpenCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourseId);

        $existIds = array();

        if (!empty($existRecommendCourses)) {
            foreach ($existRecommendCourses as $existRecommendCourse) {
                if ($existRecommendCourse['origin'] == 'open_course') {
                    $existIds['openCourse'][] = $existRecommendCourse['recommendCourseId'];
                } elseif ($existRecommendCourse['origin'] == 'course') {
                    $existIds['course'][] = $existRecommendCourse['recommendCourseId'];
                }
            }
        }

        $existIds['openCourse'][] = $openCourseId;

        return $existIds;
    }

    protected function getOpenCourseService()
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

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    protected function getLiveCourseService()
    {
        return $this->getServiceKernel()->createService('Course.LiveCourseService');
    }
}
