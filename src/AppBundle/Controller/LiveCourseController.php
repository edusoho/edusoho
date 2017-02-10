<?php
namespace AppBundle\Controller;

use Topxia\Common\Paginator;
use Biz\Util\EdusohoLiveClient;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class LiveCourseController extends BaseController
{
    public function liveCapacityAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $client       = new EdusohoLiveClient();
        $liveCapacity = $client->getCapacity();

        return $this->createJsonResponse($liveCapacity);
    }

    public function ratingCoursesBlockAction()
    {
        $conditions = array(
            'status'            => 'published',
            'type'              => 'live',
            'parentId'          => '0',
            'ratingGreaterThan' => 0.01
        );

        $courses = $this->getCourseService()->searchCourses($conditions, 'Rating', 0, 10);

        return $this->render('liveCourse/rating-courses-block.html.twig', array(
            'courses' => $courses
        ));
    }

    public function getClassroomUrlAction(Request $request, $courseId, $lessonId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('尚未登入！'));
        }

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('课时不存在！'));
        }

        if (empty($lesson['mediaId'])) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('直播教室不存在！'));
        }

        if ($lesson['startTime'] - time() > 7200) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('直播还没开始!'));
        }

        if ($lesson['endTime'] < time()) {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('直播已结束!'));
        }

        $params = array(
            'liveId'   => $lesson['mediaId'],
            'provider' => $lesson['liveProvider'],
            'user'     => $user['email'],
            'nickname' => $user['nickname']
        );

        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $user['id'])) {
            $params['role'] = 'teacher';
        } elseif ($this->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
            $params['role'] = 'student';
        } else {
            throw $this->createAccessDeniedException($this->getServiceKernel()->trans('您不是课程学员，不能参加直播！'));
        }

        $client = new EdusohoLiveClient();
        $result = $client->getRoomUrl($params);

        if (empty($result) || isset($result['error'])) {
            return $this->createJsonResponse($result);
        }

        return $this->createJsonResponse(array(
            'url'   => $result['url'],
            'param' => isset($result['param']) ? $result['param'] : null
        ));
    }

    public function entryAction(Request $request, $courseId, $lessonId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你好像忘了登录哦？'), null, 3000, $this->generateUrl('login'));
        }

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('课时不存在！'));
        }

        if (empty($lesson['mediaId'])) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('直播教室不存在！'));
        }

        if ($lesson['startTime'] - time() > 7200) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('直播还没开始!'));
        }

        if ($lesson['endTime'] < time()) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('直播已结束!'));
        }

        $params = array();

        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $user['id'])) {
            $teachers = $this->getCourseService()->findCourseTeachers($courseId);
            $teacher  = array_shift($teachers);

            if ($teacher['userId'] == $user['id']) {
                $params['role'] = 'teacher';
            } else {
                $params['role'] = 'speaker';
            }
        } elseif ($this->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
            $params['role'] = 'student';
        } else {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('您不是课程学员，不能参加直播！'));
        }

        $params['id']       = $user['id'];
        $params['nickname'] = $user['nickname'];
        return $this->forward('AppBundle:Liveroom:_entry', array('roomId' => $lesson['mediaId']), $params);
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

    public function replayCreateAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $resultList = $this->getCourseService()->generateLessonReplay($courseId, $lessonId);

        if (array_key_exists("error", $resultList)) {
            return $this->createJsonResponse($resultList);
        }

        $lesson              = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $lesson["isEnd"]     = intval(time() - $lesson["endTime"]) > 0;
        $lesson["canRecord"] = $this->_canRecord($lesson['mediaId']);

        $client = new EdusohoLiveClient();

        if ($lesson['type'] == 'live') {
            $result = $client->getMaxOnline($lesson['mediaId']);
            $this->getCourseService()->setCourseLessonMaxOnlineNum($lesson['id'], $result['onLineNum']);
        }

        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:list-item.html.twig', array(
            'course' => $this->getCourseService()->getCourse($courseId),
            'lesson' => $lesson
        ));
    }

    public function uploadModalAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

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
            $this->getCourseService()->generateLessonVideoReplay($courseId, $lessonId, $fileId);
        }

        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:upload-modal.html.twig', array(
            'course'     => $course,
            'lesson'     => $lesson,
            'targetType' => 'courselesson'
        ));
    }

    public function entryReplayAction(Request $request, $courseId, $lessonId, $courseLessonReplayId)
    {
        $course = $this->getCourseService()->tryTakeCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        return $this->render("liveCourse/classroom.html.twig", array(
            'lesson' => $lesson,
            'url'    => $this->generateUrl('live_classroom_replay_url', array(
                'courseId'             => $courseId,
                'lessonId'             => $lessonId,
                'courseLessonReplayId' => $courseLessonReplayId
            ))
        ));
    }

    /**
     * [playESLiveReplayAction 播放ES直播回放]
     */
    public function playESLiveReplayAction(Request $request, $courseId, $lessonId, $courseLessonReplayId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $replay = $this->getCourseService()->getCourseLessonReplay($courseLessonReplayId);

        return $this->forward('MaterialLibBundle:GlobalFilePlayer:player', array('globalId' => $replay['globalId']));
    }

    public function getReplayUrlAction(Request $request, $courseId, $lessonId, $courseLessonReplayId)
    {
        $ssl = $request->isSecure() ? true : false;

        $course = $this->getCourseService()->tryTakeCourse($courseId);
        $result = $this->getCourseService()->entryReplay($lessonId, $courseLessonReplayId, $ssl);

        return $this->createJsonResponse(array(
            'url'   => $result['url'],
            'param' => isset($result['param']) ? $result['param'] : null
        ));
    }

    public function replayManageAction(Request $request, $id)
    {
        $course      = $this->getCourseService()->tryManageCourse($id);
        $courseItems = $this->getCourseService()->getCourseItems($course['id']);

        foreach ($courseItems as $key => $item) {
            if ($item["itemType"] == "lesson") {
                $item["isEnd"]     = intval(time() - $item["endTime"]) > 0;
                $item["canRecord"] = !($item['replayStatus'] == 'videoGenerated') && $this->_canRecord($item['mediaId']);
                $item['file']      = $this->getLiveReplayMedia($item);
                $courseItems[$key] = $item;
            }
        }

        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:index.html.twig', array(
            'course'  => $course,
            'items'   => $courseItems,
            'default' => $default
        ));
    }

    protected function getLiveReplayMedia($lesson)
    {
        if ($lesson['type'] == 'live' && $lesson['replayStatus'] == 'videoGenerated') {
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

    protected function getSubCategories($categoryTree, $rootCategory)
    {
        $categories = array();

        $start = false;

        foreach ($categoryTree as $treeCategory) {
            if ($start && ($treeCategory['depth'] == 1) && ($treeCategory['id'] != $rootCategory['id'])) {
                break;
            }

            if ($treeCategory['id'] == $rootCategory['id']) {
                $start = true;
            }

            if ($start == true) {
                $categories[] = $treeCategory;
            }
        }

        return $categories;
    }

    private function _searchReplayLiveCourse($request, $conditions, $allFurtureLiveCourseIds, $pageFurtureLiveCourses)
    {
        $pageSize    = 10;
        $currentPage = $request->query->get('page', 1);

        $futureLiveCoursesCount = 0;

        if (isset($conditions['courseIds'])) {
            $futureLiveCoursesCount = $this->getCourseService()->searchCourseCount($conditions);
        }

        $pages = $futureLiveCoursesCount <= $pageSize ? 1 : floor($futureLiveCoursesCount / $pageSize);

        if ($pages == $currentPage) {
            $start = 0;
            $limit = $pageSize - ($futureLiveCoursesCount % $pageSize);
        } else {
            $start = ($currentPage - 1) * $pageSize;
            $limit = $pageSize;
        }

        $replayLiveLessonCourses = $this->getCourseService()->findPastLiveCourseIds();
        $replayLiveCourseIds     = ArrayToolkit::column($replayLiveLessonCourses, 'courseId');

        unset($conditions['courseIds']);
        $conditions['excludeIds'] = $allFurtureLiveCourseIds;

        $replayLiveCourses = $this->getCourseService()->searchCourses($conditions, array('createdTime', 'DESC'), $start, $limit);

        $replayLiveCourses = ArrayToolkit::index($replayLiveCourses, 'id');
        $replayLiveCourses = $this->_liveCourseSort($replayLiveCourseIds, $replayLiveCourses, 'replay');

        return $replayLiveCourses;
    }

    private function _liveCourseSort($liveLessonCourseIds, $liveCourses, $type)
    {
        $courses = array();

        if (empty($liveCourses)) {
            return array();
        }

        foreach ($liveLessonCourseIds as $key => $courseId) {
            if (isset($liveCourses[$courseId])) {
                $courses[$courseId] = $liveCourses[$courseId];

                if ($type == 'furture') {
                    $lessons = $this->getCourseService()->searchLessons(array('courseId' => $courseId, 'endTimeGreaterThan' => time()), array('startTime', 'ASC'), 0, 1);
                } else {
                    $lessons = $this->getCourseService()->searchLessons(array('courseId' => $courseId, 'endTimeLessThan' => time()), array('startTime', 'DESC'), 0, 1);
                }

                $courses[$courseId]['liveStartTime'] = $lessons[0]['startTime'];
                $courses[$courseId]['liveEndTime']   = $lessons[0]['endTime'];
                $courses[$courseId]['lessonId']      = $lessons[0]['id'];
            }
        }

        return $courses;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    public function getLevelService()
    {
        return $this->getServiceKernel()->createService('VipPlugin:Vip:LevelService');
    }

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File:UploadFileService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }
}
