<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OpenCourseController extends BaseController
{
    public function exploreAction(Request $request)
    {
        var_dump($request->cookies->get('refererLogToken'));
        return $this->render('TopxiaWebBundle:OpenCourse:explore.html.twig');
    }

    public function pageItemAction(Request $request)
    {
        $queryParam = $request->query->all();
        $conditions = $this->_filterConditions($queryParam);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getOpenCourseService()->searchCourseCount($conditions),
            10
        );

        $paginator->setBaseUrl($this->generateUrl('open_course_explore_list'));

        $courses  = $this->_getPageRecommendedCourses($request, $conditions, 'recommendedSeq', 10);
        $teachers = $this->findCourseTeachers($courses);

        return $this->render('TopxiaWebBundle:OpenCourse/Widget:open-course-grid-horizontal.html.twig', array(
            'courses'   => $courses,
            'paginator' => $paginator,
            'teachers'  => $teachers
        ));
    }

    public function createAction(Request $request)
    {
        $course = $request->request->all();
        unset($course['buyable']);

        $course = $this->getOpenCourseService()->createCourse($course);

        return $this->redirect($this->generateUrl('open_course_manage', array('id' => $course['id'])));
    }

    public function showAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        if ($request->query->get('as') === 'preview') {
            $this->getOpenCourseService()->tryManageOpenCourse($courseId);

            if (!$this->_checkPublishedLessonExists($courseId)) {
                $message = $course['type'] == 'liveOpen' ? '请先设置直播时间！' : '请先创建课时并发布！';
                return $this->createMessageResponse('error', $message);
            }

            return $this->render("TopxiaWebBundle:OpenCourse:open-course-show.html.twig", array(
                'course' => $course
            ));
        }

        if (!$this->_checkCourseStatus($courseId)) {
            return $this->createMessageResponse('error', '课程不存在，或未发布。');
        }

        if (!$this->_checkPublishedLessonExists($courseId)) {
            return $this->createMessageResponse('error', '请先创建课时并发布！');
        }

        $course = $this->getOpenCourseService()->waveCourse($courseId, 'hitNum', +1);

        $member = $this->_memberOperate($request, $courseId);

        $response = $this->render("TopxiaWebBundle:OpenCourse:open-course-show.html.twig", array(
            'course'   => $course,
            'lessonId' => $lessonId
        ));

        $this->createRefererLog($request, $response, $course);

        return $response;
    }

    public function lessonShowAction(Request $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if (!$lesson) {
            return $this->createMessageResponse('error', '该课时不存在！');
        }

        if ($lesson['mediaId'] && $lesson['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!$file) {
                return $this->createJsonResponse(array('mediaError' => '该课时为无效课时，不能播放'));
            }
        } elseif ($lesson['mediaId'] == 0 && $lesson['mediaSource'] == 'self') {
            return $this->createJsonResponse(array('mediaError' => '该课时为无效课时，不能播放'));
        }

        $lesson = $this->_getLessonVedioInfo($request, $lesson);

        return $this->createJsonResponse($lesson);
    }

    /**
     * Block Actions.
     */
    public function headerAction(Request $request, $course, $lessonId)
    {
        if ($lessonId) {
            $lesson = $this->getOpenCourseService()->getCourseLesson($course['id'], $lessonId);

            if (!$lesson || ($lesson && $lesson['status'] != 'published')) {
                $lesson = array();
            }
        } else {
            $lesson = $this->_checkPublishedLessonExists($course['id']);
        }

        $lesson     = $lesson ? $this->_getLessonVedioInfo($request, $lesson) : array();
        $nextLesson = $this->getOpenCourseService()->getNextLesson($course['id'], $lesson['id']);
        $member     = $this->_getMember($request, $course['id']);

        $lesson['replays'] = $this->_getLiveReplay($lesson);

        $notifyNum = $this->getOpenCourseService()->searchMemberCount(array('courseId' => $course['id'], 'isNotified' => 1));

        return $this->render('TopxiaWebBundle:OpenCourse:open-course-header.html.twig', array(
            'course'     => $course,
            'lesson'     => $lesson,
            'member'     => $member,
            'notifyNum'  => $notifyNum,
            'nextLesson' => $nextLesson
        ));
    }

    public function teachersAction($courseId)
    {
        $course         = $this->getOpenCourseService()->getCourse($courseId);
        $teachersNoSort = $this->getUserService()->findUsersByIds($course['teacherIds']);

        $teachers = array();

        if (!empty($course['teacherIds'][0])) {
            foreach ($course['teacherIds'] as $key => $teacherId) {
                $teachers[$teacherId] = $teachersNoSort[$teacherId];
            }
        }

        $profiles = $this->getUserService()->findUserProfilesByIds($course['teacherIds']);

        return $this->render('TopxiaWebBundle:OpenCourse:open-course-teacher-block.html.twig', array(
            'course'   => $course,
            'teachers' => $teachers,
            'profiles' => $profiles
        ));
    }

    public function infoBarAction(Request $request, $courseId)
    {
        $course                = $this->getOpenCourseService()->getCourse($courseId);
        $course['favoriteNum'] = $this->_getFavoriteNum($courseId);

        $member = $this->_getMember($request, $course['id']);

        return $this->render('TopxiaWebBundle:OpenCourse:open-course-info-bar-block.html.twig', array(
            'course' => $course,
            'member' => $member
        ));
    }

    public function favoriteAction(Request $request, $id)
    {
        try {
            $favoriteNum = $this->getOpenCourseService()->favoriteCourse($id);
            $jsonData    = array('result' => true, 'number' => $favoriteNum);
        } catch (\Exception $e) {
            $jsonData = array('result' => false, 'message' => $e->getMessage());
        }

        return $this->createJsonResponse($jsonData);
    }

    public function unfavoriteAction(Request $request, $id)
    {
        try {
            $favoriteNum = $this->getOpenCourseService()->unFavoriteCourse($id);
            $jsonData    = array('result' => true, 'number' => $favoriteNum);
        } catch (\Exception $e) {
            $jsonData = array('result' => false, 'message' => $e->getMessage());
        }
        return $this->createJsonResponse($jsonData);
    }

    public function likeAction(Request $request, $id)
    {
        if (!$this->_checkCourseStatus($id)) {
            return $this->createJsonResponse(array('result' => false));
        }

        $course = $this->getOpenCourseService()->waveCourse($id, 'likeNum', +1);

        return $this->createJsonResponse(array('result' => true, 'number' => $course['likeNum']));
    }

    public function unlikeAction(Request $request, $id)
    {
        if (!$this->_checkCourseStatus($id)) {
            return $this->createJsonResponse(array('result' => false));
        }

        $course = $this->getOpenCourseService()->waveCourse($id, 'likeNum', -1);

        return $this->createJsonResponse(array('result' => true, 'number' => $course['likeNum']));
    }

    public function qrcodeAction(Request $request, $id)
    {
        $user  = $this->getUserService()->getCurrentUser();
        $host  = $request->getSchemeAndHttpHost();
        $token = $this->getTokenService()->makeToken('qrcode', array(
            'userId'   => $user['id'],
            'data'     => array(
                'url'    => $this->generateUrl('open_course_show', array('courseId' => $id), true),
                'appUrl' => ""
            ),
            'times'    => 0,
            'duration' => 3600
        ));
        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        $response = array(
            'img' => $this->generateUrl('common_qrcode', array('text' => $url), true)
        );
        return $this->createJsonResponse($response);
    }

    public function commentAction(Request $request, $courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        if (!$course) {
            return $this->createMessageResponse('error', '课程不存在，或未发布。');
        }

        $conditions = array(
            'targetId'   => $course['id'],
            'targetType' => 'openCourse',
            'parentId'   => 0
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchPostsCount($conditions),
            10
        );

        $posts = $this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        return $this->render('TopxiaWebBundle:OpenCourse:open-course-comment.html.twig', array(
            'course'    => $course,
            'posts'     => $posts,
            'users'     => $users,
            'paginator' => $paginator,
            'service'   => $this->getThreadService()
        ));
    }

    public function postAction(Request $request, $id)
    {
        if (!$this->_checkCourseStatus($id)) {
            return $this->createMessageResponse('error', '课程不存在，或未发布。');
        }

        return $this->forward('TopxiaWebBundle:Thread:postSave', array(
            'request'    => $request,
            'targetType' => 'openCourse',
            'targetId'   => $id
        ));
    }

    public function postReplyAction(Request $request, $id, $postId)
    {
        if (!$this->_checkCourseStatus($id)) {
            return $this->createMessageResponse('error', '课程不存在，或未发布。');
        }

        $fields               = $request->request->all();
        $fields['content']    = $this->autoParagraph($fields['content']);
        $fields['targetId']   = $id;
        $fields['parentId']   = $postId;
        $fields['targetType'] = 'openCourse';

        $post = $this->getThreadService()->createPost($fields);

        return $this->render('TopxiaWebBundle:Thread:subpost-item.html.twig', array(
            'post'    => $post,
            'author'  => $this->getCurrentUser(),
            'service' => $this->getThreadService()
        ));
    }

    public function memberSmsAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);
        $user   = $this->getCurrentUser();

        if (!$course) {
            return $this->createJsonResponse(array('result' => false, 'message' => '该课程不存在或已删除！'));
        }

        $smsSetting = $this->setting('cloud_sms', array());

        if (!$user->isLogin() && !$smsSetting['sms_enabled']) {
            throw $this->createAccessDeniedException();
        }

        if ($request->getMethod() == 'POST') {
            $member = $this->_memberOperate($request, $id);

            $fields               = $request->request->all();
            $fields['isNotified'] = 1;
            $member               = $this->getOpenCourseService()->updateMember($member['id'], $fields);

            $this->_loginMemberMobileBind($fields['mobile']);

            $memberNum = $this->getOpenCourseService()->searchMemberCount(array('courseId' => $id, 'isNotified' => 1));

            return $this->createJsonResponse(array('result' => true, 'number' => $memberNum));
        }

        return $this->render('TopxiaWebBundle:OpenCourse:member-sms-modal.html.twig', array(
            'course' => $course
        ));
    }

    public function createMemberAction(Request $request, $id)
    {
        $result = $this->_checkExistsMember($request, $id);

        if (!$result['result']) {
            return $this->createJsonResponse($result);
        }

        if ($request->getMethod() == 'POST') {
            $fields             = $request->request->all();
            $fields['ip']       = $request->getClientIp();
            $fields['courseId'] = $id;

            $member    = $this->getOpenCourseService()->createMember($fields);
            $memberNum = $this->getOpenCourseService()->searchMemberCount(array('courseId' => $id));

            return $this->createJsonResponse(array('result' => true, 'number' => $memberNum));
        }
    }

    public function playerAction($courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            throw $this->createNotFoundException('课时不存在！');
        }

        return $this->forward('TopxiaWebBundle:Player:show', array(
            'id'      => $lesson["mediaId"],
            'context' => array()
        ));
    }

    public function materialListAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $conditions = array(
            'courseId'        => $id,
            'excludeLessonId' => 0,
            'source'          => 'opencoursematerial',
            'type'            => 'openCourse'
        );

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->searchMaterialCount($conditions),
            5
        );

        $materials = $this->getMaterialService()->searchMaterials(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course['id']);
        $lessons = ArrayToolkit::index($lessons, 'id');

        return $this->render("TopxiaWebBundle:OpenCourse:open-course-material-block.html.twig", array(
            'course'    => $course,
            'lessons'   => $lessons,
            'materials' => $materials,
            'paginator' => $paginator
        ));
    }

    public function materialDownloadAction(Request $request, $courseId, $materialId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);

        if (empty($material)) {
            throw $this->createNotFoundException();
        }

        return $this->forward('TopxiaWebBundle:UploadFile:download', array('fileId' => $material['fileId']));
    }

    public function mobileCheckAction(Request $request)
    {
        $user     = $this->getCurrentUser();
        $response = array('success' => true, 'message' => '');

        if ($user->isLogin()) {
            $mobile                 = $request->query->get('value');
            list($result, $message) = $this->getAuthService()->checkMobile($mobile);

            if ($result != 'success') {
                $response = array('success' => false, 'message' => $message);
            }
        }

        return $this->createJsonResponse($response);
    }

    public function adModalRecommendCourseAction(Request $request, $id)
    {
        $num        = $request->query->get('num', 3);
        $courses    = $this->getOpenCourseRecommendedService()->findRandomRecommendCourses($id, $num);
        $courses    = array_values($courses);
        $conditions = array(
            array(
                'status'      => 'published',
                'recommended' => 1,
                'parentId'    => 0
            ),
            array(
                'status'   => 'published',
                'parentId' => 0
            )
        );
        //数量不够 随机取推荐课程里的课程 还是不够随机取所有课程
        foreach ($conditions as $condition) {
            if (count($courses) < $num) {
                $needNum                 = $num - count($courses);
                $condition['excludeIds'] = ArrayToolkit::column($courses, 'id');
                $recommendCourses        = $this->getCourseService()->findRandomCourses($condition, $needNum);
                $courses                 = array_merge($courses, $recommendCourses);
            }
        }
        $self    = $this;
        $courses = array_map(function ($course) use ($self) {
            foreach (array('smallPicture', 'middlePicture', 'largePicture') as $key) {
                $course[$key] = $self->get('topxia.twig.web_extension')->getFpath($course[$key], 'course.png');
            }
            return $course;
        }, $courses);
        return $this->createJsonResponse($courses);
    }

    private function _getMember($request, $courseId)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            $member = $this->getOpenCourseService()->getCourseMember($courseId, $user['id']);
        } else {
            $member = $this->getOpenCourseService()->getCourseMemberByIp($courseId, $request->getClientIp());
        }

        return $member;
    }

    private function _getLessonVedioInfo(Request $request, $lesson)
    {
        $lesson['videoWatermarkEmbedded'] = 0;

        if ($lesson['type'] == 'video' && $lesson['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);

            $lesson['mediaConvertStatus'] = $file['convertStatus'];

            if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
                $factory = new CloudClientFactory();
                $client  = $factory->createClient();
                $hls     = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);

                if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                    $token = $this->getTokenService()->makeToken('hls.playlist', array(
                        'data'     => array(
                            'id' => $file['id']
                        ),
                        'times'    => $this->agentInWhiteList($request->headers->get("user-agent")) ? 0 : 3,
                        'duration' => 3600
                    ));

                    $hls = array(
                        'url' => $this->generateUrl('hls_playlist', array(
                            'id'    => $file['id'],
                            'token' => $token['token'],
                            'line'  => $request->query->get('line')
                        ), true)
                    );
                } else {
                    $hls = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                }

                $lesson['mediaHLSUri'] = $hls['url'];
            }

            if (!empty($file['convertParams']['hasVideoWatermark'])) {
                $lesson['videoWatermarkEmbedded'] = 1;
            }
        } elseif ($lesson['mediaSource'] == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);

            if ($matched) {
                $lesson['mediaUri']    = "http://player.youku.com/embed/{$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }
        } elseif ($lesson['mediaSource'] == 'tudou') {
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);

            if ($matched) {
                $lesson['mediaUri']    = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
                $lesson['mediaSource'] = 'iframe';
            } else {
                $lesson['mediaUri'] = $lesson['mediaUri'];
            }
        }

        if ($lesson['type'] == 'liveOpen') {
            if ($lesson['startTime'] > time()) {
                $lesson['startTimeLeft'] = $lesson['startTime'] - time();
            }
        }

        return $lesson;
    }

    private function _checkCourseStatus($courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        if (!$course || ($course && $course['status'] != 'published')) {
            return false;
        }

        return true;
    }

    private function _checkPublishedLessonExists($courseId)
    {
        $lessons = $this->getOpenCourseService()->searchLessons(array(
            'courseId' => $courseId,
            'status'   => 'published'),
            array('seq', 'ASC'), 0, 1
        );

        if (!$lessons) {
            return false;
        }

        return $lessons[0];
    }

    private function _getFavoriteNum($courseId)
    {
        $favoriteNum = $this->getCourseService()->searchCourseFavoriteCount(array(
            'courseId' => $courseId,
            'type'     => 'openCourse'
        )
        );

        return $favoriteNum;
    }

    private function _memberOperate(Request $request, $courseId)
    {
        $result = $this->_checkExistsMember($request, $courseId);

        if ($result['result']) {
            $fields = array(
                'courseId'      => $courseId,
                'ip'            => $request->getClientIp(),
                'lastEnterTime' => time()
            );
            $member = $this->getOpenCourseService()->createMember($fields);
        } else {
            $member = $this->getOpenCourseService()->updateMember($result['member']['id'], array('lastEnterTime' => time()));
        }

        return $member;
    }

    private function _checkExistsMember(Request $request, $courseId)
    {
        $user   = $this->getCurrentUser();
        $userIp = $request->getClientIp();

        if (!$user->isLogin()) {
            $openCourseMember = $this->getOpenCourseService()->getCourseMemberByIp($courseId, $userIp);
        } else {
            $openCourseMember = $this->getOpenCourseService()->getCourseMember($courseId, $user['id']);
        }

        if ($openCourseMember) {
            return array('result' => false, 'message' => '课程用户已存在！', 'member' => $openCourseMember);
        }

        return array('result' => true);
    }

    protected function autoParagraph($text)
    {
        if (trim($text) !== '') {
            $text  = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            $text  = preg_replace("/\n\n+/", "\n\n", str_replace(array("\r\n", "\r"), "\n", $text));
            $texts = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
            $text  = '';

            foreach ($texts as $txt) {
                $text .= '<p>'.nl2br(trim($txt, "\n"))."</p>\n";
            }

            $text = preg_replace('|<p>\s*</p>|', '', $text);
        }

        return $text;
    }

    private function _getLiveReplay($lesson)
    {
        $replays = array();

        if ($lesson['type'] == 'liveOpen') {
            $replays = $this->getCourseService()->searchCourseLessonReplays(array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'hidden'   => 0,
                'type'     => 'liveOpen'
            ), array('createdTime', 'DESC'), 0, PHP_INT_MAX);
        }

        return $replays;
    }

    private function _getPageRecommendedCourses(Request $request, $conditions, $orderBy, $pageSize)
    {
        $conditions['recommended'] = 1;

        $recommendCount = $this->getOpenCourseService()->searchCourseCount($conditions);
        $currentPage    = $request->query->get('page') ? $request->query->get('page') : 1;
        $recommendPage  = intval($recommendCount / $pageSize);
        $recommendLeft  = $recommendCount % $pageSize;

        $currentPageCourses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('recommendedSeq', 'ASC'),
            ($currentPage - 1) * $pageSize,
            $pageSize
        );

        if (count($currentPageCourses) == 0) {
            $start = ($pageSize - $recommendLeft) + ($currentPage - $recommendPage - 2) * $pageSize;
            $limit = $pageSize;
        } elseif (count($currentPageCourses) > 0 && count($currentPageCourses) <= $pageSize) {
            $start = 0;
            $limit = $pageSize - count($currentPageCourses);
        }

        $conditions['recommended'] = 0;

        $courses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('createdTime', 'DESC'),
            $start, $limit
        );

        return array_merge($currentPageCourses, $courses);
    }

    private function _filterConditions($queryParam)
    {
        $conditions = array('status' => 'published');

        if (!empty($queryParam['fliter']['type']) && $queryParam['fliter']['type'] != 'all') {
            $conditions['type'] = $queryParam['fliter']['type'];
        }

        /*if (isset($queryParam['orderBy']) && $queryParam['orderBy'] == 'recommendedSeq') {
        $conditions['recommended'] = 1;
        }*/

        return $conditions;
    }

    private function _loginMemberMobileBind($userMobile)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin() && empty($user['verifiedMobile'])) {
            $this->getUserService()->changeMobile($user['id'], $userMobile);
        }

        return true;
    }

    protected function findCourseTeachers($courses)
    {
        if (!$courses) {
            return array();
        }

        $userIds = array();
        foreach ($courses as $key => $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        return $this->getUserService()->findUsersByIds($userIds);
    }

    protected function createRefererLog(Request $request, Response $response, $course)
    {
        $refererUrl = $request->server->get('HTTP_REFERER');
        $refererUrl = empty($refererUrl) ? $request->getUri() : $refererUrl;

        $refererLogToken = $this->getRefererLogToken($request, $response);

        $fields = array(
            'targetId'        => $course['id'],
            'targetType'      => 'openCourse',
            'refererUrl'      => $refererUrl,
            'targetInnerType' => $course['type'],
            'token'           => $refererLogToken,
            'ip'              => $request->getClientIp()
        );

        $refererLog = $this->getRefererLogService()->addRefererLog($fields);

        return $refererLog;
    }

    protected function getRefererLogToken(Request $request, Response $response)
    {
        $refererLogToken = $request->cookies->get('refererLogToken');

        if (empty($refererLogToken)) {
            $refererLogToken = 'refererLog/'.date('Y-m-d');

            $expire = strtotime(date('Y-m-d').' 23:59:59') - time();

            //setCookie('refererLogToken', $refererLogToken); //, time() + $expire
            $response->headers->setCookie(new Cookie("refererLogToken", $refererLogToken));
            $response->send();
        }

        return $refererLogToken;
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getRefererLogService()
    {
        return $this->getServiceKernel()->createService('RefererLog.RefererLogService');
    }

    protected function getOpenCourseRecommendedService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseRecommendedService');
    }
}
