<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\OpenCourse\CourseProcessor\CourseProcessorFactory;

class OpenCourseManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
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

        //获取直播供应商
        $client   = new EdusohoLiveClient();
        $capacity = $client->getCapacity();

        return $this->render('TopxiaWebBundle:CourseManage:teachers.html.twig', array(
            'course'   => $course,
            'teachers' => $teachers,
            'capacity' => $capacity
        ));
    }

    public function studentsAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $fields             = $request->query->all();
        $fields['userType'] = isset($fields['userType']) ? $fields['userType'] : 'login';

        $condition = array('courseId' => $course['id'], 'role' => 'student');

        if ($fields['userType'] == 'login') {
            $condition['userIdGT'] = 0;
        } elseif ($fields['userType'] == 'unlogin') {
            $condition['userId'] = 0;
        }

        if (isset($fields['isNotified']) && $fields['isNotified'] == 1) {
            $condition['isNotified'] = 1;
        }

        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $users = $this->getUserService()->searchUsers(
                array('nickname' => $fields['keyword']),
                array('createdTime', 'DESC'),
                0, PHP_INT_MAX
            );
            $userIds              = ArrayToolkit::column($users, 'id');
            $condition['userIds'] = $userIds ? $userIds : array(-1);
        }

        $paginator = new Paginator(
            $request,
            $this->getOpenCourseService()->searchMemberCount($condition),
            20
        );

        $students = $this->getOpenCourseService()->searchMembers(
            $condition,
            array('lastEnterTime', 'DESC'),
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

            if (!isset($liveLessonFields['startTime']) || empty($liveLessonFields['startTime'])) {
                return $this->createMessageResponse('error', '请先设置直播时间。');
            }

            $liveLesson['type']      = 'liveOpen';
            $liveLesson['courseId']  = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLessonFields['startTime']);
            $liveLesson['length']    = $liveLessonFields['timeLength'];
            $liveLesson['title']     = $liveCourse['title'];

            if ($openLiveLesson) {
                $live       = $this->getLiveCourseService()->editLiveRoom($liveCourse, $liveLesson, $this->container);
                $liveLesson = $this->getOpenCourseService()->updateLesson($liveLesson['courseId'], $liveLesson['id'], $liveLesson);
            } else {
                $live = $this->getLiveCourseService()->createLiveRoom($liveCourse, $liveLesson, $this->container);

                $liveLesson['mediaId']      = $live['id'];
                $liveLesson['liveProvider'] = $live['provider'];

                $liveLesson = $this->getOpenCourseService()->createLesson($liveLesson);
            }

            $this->setFlashMessage('success', '直播时间设置已保存！');
        }

        return $this->render('TopxiaWebBundle:OpenCourseManage:live-open-time-set.html.twig', array(
            'course'         => $liveCourse,
            'openLiveLesson' => $liveLesson
        ));
    }

    public function marketingAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        if ($request->getMethod() == 'POST') {
            $recommendIds = $request->request->get('recommendIds');

            $this->getOpenCourseRecommendedService()->updateOpenCourseRecommendedCourses($id, $recommendIds);

            $this->setFlashMessage('success', "推荐课程修改成功");

            return $this->redirect($this->generateUrl('open_course_manage_marketing', array(
                'id' => $id
            )));
        }

        $recommends = $this->getOpenCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($id);

        $recommendedCourses = array();

        foreach ($recommends as $key => $recommend) {
            $recommendedCourses[$recommend['id']] = $this->getTypeCourseService($recommend['type'])->getCourse($recommend['recommendCourseId']);
        }

        $users = $this->_getTeacherUsers($recommendedCourses);

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-marketing.html.twig', array(
            'courses' => $recommendedCourses,
            'users'   => $users,
            'course'  => $course
        ));
    }

    public function pickAction(Request $request, $filter, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $conditions = $request->query->all();

        list($paginator, $courses) = $this->_getPickCourseData($request, $id, $conditions);

        $users = $this->_getTeacherUsers($courses);

        return $this->render('TopxiaWebBundle:OpenCourseManage:open-course-pick-modal.html.twig', array(
            'users'     => $users,
            'courses'   => $courses,
            'paginator' => $paginator,
            'courseId'  => $id,
            'filter'    => $filter
        ));
    }

    public function deleteRecommendCourseAction(Request $request, $id, $recommendId)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($id);
        $this->getOpenCourseRecommendedService()->deleteRecommendCourse($recommendId);
        return $this->createJsonResponse(true);
    }

    public function searchAction(Request $request, $id, $filter)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $conditions = array("title" => $request->request->get('key'));

        list($paginator, $courses) = $this->_getPickCourseData($request, $id, $conditions);

        $users = $this->_getTeacherUsers($courses);

        return $this->render('TopxiaWebBundle:Course:course-select-list.html.twig', array(
            'users'   => $users,
            'courses' => $courses,
            'filter'  => $filter
        ));
    }

    public function recommendedCoursesSelectAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $recommendNum = $this->getOpenCourseRecommendedService()->searchRecommendCount(array('openCourseId' => $id));

        $ids = $request->request->get('ids');

        if (empty($ids)) {
            return $this->createJsonResponse(array('result' => true));
        }

        if (($recommendNum + count($ids)) > 5) {
            return $this->createJsonResponse(array('result' => false, 'message' => '推荐课程数量不能超过5个！'));
        }

        $this->getOpenCourseRecommendedService()->addRecommendedCourses($id, $ids, 'normal');

        return $this->createJsonResponse(array('result' => true));
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

    public function studentsExportAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $gender = array('female' => '女', 'male' => '男', 'secret' => '秘密');

        $conditions = array('courseId' => $course['id'], 'role' => 'student');

        $userType = $request->query->get('userType', '');
        if ($userType == 'login') {
            $conditions['userIdGT'] = 0;
        } elseif ($userType == 'unlogin') {
            $conditions['userId'] = 0;
        }

        if ($request->query->get('isNotified', 0) == 1) {
            $conditions['isNotified'] = 1;
        }

        $courseMembers = $this->getOpenCourseService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, 20000);

        $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        $fields['weibo'] = "微博";

        foreach ($userFields as $userField) {
            $fields[$userField['fieldName']] = $userField['title'];
        }

        $studentUserIds = ArrayToolkit::column($courseMembers, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $progresses = array();

        $str = "用户名,Email,手机号,加入学习时间,上次进入时间,IP,姓名,性别,QQ号,微信号,公司,职业,头衔";

        foreach ($fields as $key => $value) {
            $str .= ",".$value;
        }

        $str .= "\r\n";

        $students = array();

        foreach ($courseMembers as $courseMember) {
            $member = "";

            if ($userType == 'login') {
                $member .= $users[$courseMember['userId']]['nickname'].",";
                $member .= $users[$courseMember['userId']]['email'].",";
                $member .= $users[$courseMember['userId']]['verifiedMobile'] ? $users[$courseMember['userId']]['verifiedMobile']."," : "-,";
                $member .= date('Y-n-d H:i:s', $courseMember['createdTime']).",";
                $member .= date('Y-n-d H:i:s', $courseMember['lastEnterTime']).",";
                $member .= $courseMember['ip'].",";
                $member .= $profiles[$courseMember['userId']]['truename'] ? $profiles[$courseMember['userId']]['truename']."," : "-".",";
                $member .= $gender[$profiles[$courseMember['userId']]['gender']].",";
                $member .= $profiles[$courseMember['userId']]['qq'] ? $profiles[$courseMember['userId']]['qq']."," : "-".",";
                $member .= $profiles[$courseMember['userId']]['weixin'] ? $profiles[$courseMember['userId']]['weixin']."," : "-".",";
                $member .= $profiles[$courseMember['userId']]['company'] ? $profiles[$courseMember['userId']]['company']."," : "-".",";
                $member .= $profiles[$courseMember['userId']]['job'] ? $profiles[$courseMember['userId']]['job']."," : "-".",";
                $member .= $users[$courseMember['userId']]['title'] ? $users[$courseMember['userId']]['title']."," : "-".",";

                foreach ($fields as $key => $value) {
                    $member .= $profiles[$courseMember['userId']][$key] ? $profiles[$courseMember['userId']][$key]."," : "-".",";
                }
            } else {
                $member .= "-,-,";
                $member .= $courseMember['mobile'] ? $courseMember['mobile'].',' : '-,';
                $member .= date('Y-n-d H:i:s', $courseMember['createdTime']).",";
                $member .= date('Y-n-d H:i:s', $courseMember['lastEnterTime']).",";
                $member .= $courseMember['ip'].",";
                $member .= "-,-,-,-,-,-,-,";
                $member .= str_repeat('-,', count($fields) - 1).'-,';
            }

            $students[] = $member;
        };

        $str .= implode("\r\n", $students);
        $str = chr(239).chr(187).chr(191).$str;

        $filename = sprintf("open-course-%s-students-(%s).csv", $course['id'], date('Y-n-d'));

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    public function lessonTimeCheckAction(Request $request, $courseId)
    {
        $data = $request->query->all();

        $startTime = $data['startTime'];
        $length    = $data['length'];
        $lessonId  = empty($data['lessonId']) ? "" : $data['lessonId'];

        list($result, $message) = $this->getOpenCourseService()->liveLessonTimeCheck($courseId, $lessonId, $startTime, $length);

        if ($result == 'success') {
            $response = array('success' => true, 'message' => '这个时间段的课时可以创建');
        } else {
            $response = array('success' => false, 'message' => $message);
        }

        return $this->createJsonResponse($response);
    }

    private function _getPickCourseData(Request $request, $openCourseId, $conditions)
    {
        $existRecommendCourseIds = $this->getExistRecommendCourseIds($openCourseId);

        $conditions = $this->_filterConditions($conditions, $existRecommendCourseIds);

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchCourseCount($conditions),
            5
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            array('createdTime', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return array($paginator, $courses);
    }

    private function getExistRecommendCourseIds($openCourseId)
    {
        $coursesRecommended = $this->getOpenCourseRecommendedService()->searchRecommends(
            array('openCourseId' => $openCourseId),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        $existIds = ArrayToolkit::column($coursesRecommended, 'recommendCourseId');

        return $existIds;
    }

    private function _getTeacherUsers($courses)
    {
        $userIds = array();

        foreach ($courses as &$course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $users;
    }

    private function _filterConditions($conditions, $excludeCourseIds)
    {
        $conditions['status']   = 'published';
        $conditions['parentId'] = 0;

        if (!empty($excludeCourseIds)) {
            $conditions['excludeIds'] = $excludeCourseIds;
        }

        if (isset($conditions['title']) && $conditions['title'] == "") {
            unset($conditions['title']);
        }

        return $conditions;
    }

    private function _getType($filter)
    {
        $type = 'open';

        if ($filter == 'openCourse') {
            $type = 'open';
        } elseif ($filter == 'otherCourse' || $filter == 'normal') {
            $type = 'normal';
        }

        return $type;
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getOpenCourseRecommendedService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseRecommendedService');
    }

    protected function getTypeCourseService($type)
    {
        return CourseProcessorFactory::create($type);
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

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
