<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Component\MediaParser\ParserProxy;
use Biz\Course\MaterialException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\OpenCourseRecommendedService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\TagService;
use Biz\Thread\Service\ThreadService;
use Biz\User\Service\AuthService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OpenCourseController extends BaseOpenCourseController
{
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
        $preview = $request->query->get('as');
        $isWxPreview = 'preview' === $request->query->get('as') && 'wx' === $request->query->get('previewType');
        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'openCourse', 'ownerId' => $courseId));

        $tagIds = ArrayToolkit::column($tags, 'id');

        if ($isWxPreview || $this->isWxClient()) {
            $template = 'open-course/mobile/open-course-show.html.twig';
        } else {
            $template = 'open-course/open-course-show.html.twig';
        }

        if ('preview' === $preview) {
            $this->getOpenCourseService()->tryManageOpenCourse($courseId);

            return $this->render($template, array(
                'tagIds' => $tagIds,
                'course' => $course,
                'wxPreviewUrl' => $this->getWxPreviewQrCodeUrl($course['id']),
            ));
        }

        if (!$this->_checkCourseStatus($courseId)) {
            return $this->createMessageResponse('error', '课程暂时无法查看，请稍后再试。');
        }

        $member = $this->_memberOperate($request, $courseId);
        $course = $this->getOpenCourseService()->waveCourse($courseId, 'hitNum', +1);

        $response = $this->renderView($template, array(
            'tagIds' => $tagIds,
            'course' => $course,
            'lessonId' => $lessonId,
        ));
        $response = new Response($response);

        if (!$request->cookies->get('uv')) {
            $expire = strtotime(date('Y-m-d').' 23:59:59');
            $response->headers->setCookie(new Cookie('uv', uniqid($prefix = 'refererToken'), $expire));
            //$response->send();
        }

        if ('liveOpen' != $course['type']) {
            $this->createRefererLog($request, $course);
        }

        return $response;
    }

    public function lessonShowAction(Request $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if (!$lesson) {
            return $this->createMessageResponse('error', '该课时不存在！');
        }

        if ($lesson['mediaId'] && 'self' == $lesson['mediaSource']) {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            if (!$file) {
                return $this->createJsonResponse(array('mediaError' => '该课时为无效课时，不能播放'));
            }
        } elseif (0 == $lesson['mediaId'] && 'self' == $lesson['mediaSource']) {
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
        $isWxPreview = 'preview' === $request->query->get('as') && 'wx' === $request->query->get('previewType');
        if ($isWxPreview || $this->isWxClient()) {
            $template = 'open-course/mobile/open-course-header.html.twig';
        } else {
            $template = 'open-course/open-course-header.html.twig';
        }

        if ($lessonId) {
            $lesson = $this->getOpenCourseService()->getCourseLesson($course['id'], $lessonId);

            if (!$lesson || ($lesson && 'published' != $lesson['status'])) {
                $lesson = array();
            }
        } else {
            $lesson = $this->_checkPublishedLessonExists($course['id']);
        }

        $lesson = $lesson ? $this->_getLessonVedioInfo($request, $lesson) : array();
        //$nextLesson = $this->getOpenCourseService()->getNextLesson($course['id'], $lesson['id']);
        $member = $this->_getMember($course['id']);
        if ($lesson) {
            $lesson['replays'] = $this->_getLiveReplay($lesson);
        }

        $notifyNum = $this->getOpenCourseService()->countMembers(array('courseId' => $course['id'], 'isNotified' => 1));

        return $this->render($template, array(
            'course' => $course,
            'lesson' => $lesson,
            'member' => $member,
            'notifyNum' => $notifyNum,
            // 'nextLesson' => $nextLesson
        ));
    }

    public function teachersAction($courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $teachersNoSort = $this->getUserService()->findUsersByIds($course['teacherIds']);

        $teachers = array();

        if (!empty($course['teacherIds'][0])) {
            foreach ($course['teacherIds'] as $key => $teacherId) {
                $teachers[$teacherId] = $teachersNoSort[$teacherId];
            }
        }

        $profiles = $this->getUserService()->findUserProfilesByIds($course['teacherIds']);

        return $this->render('open-course/open-course-teacher-block.html.twig', array(
            'course' => $course,
            'teachers' => $teachers,
            'profiles' => $profiles,
        ));
    }

    public function infoBarAction(Request $request, $courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        $member = $this->_getMember($course['id']);

        $user = $this->getCurrentUser();
        $memberFavorite = $this->getOpenCourseService()->getFavoriteByUserIdAndCourseId($user['id'], $courseId, 'openCourse');

        return $this->render('open-course/info-bar-block.html.twig', array(
            'course' => $course,
            'member' => $member,
            'memberFavorite' => $memberFavorite,
        ));
    }

    public function favoriteAction(Request $request, $id)
    {
        $favoriteNum = $this->getOpenCourseService()->favoriteCourse($id);
        $jsonData = array('result' => true, 'number' => $favoriteNum);

        return $this->createJsonResponse($jsonData);
    }

    public function unfavoriteAction(Request $request, $id)
    {
        $favoriteNum = $this->getOpenCourseService()->unFavoriteCourse($id);
        $jsonData = array('result' => true, 'number' => $favoriteNum);

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

    protected function getWxPreviewQrCodeUrl($id)
    {
        $user = $this->getUserService()->getCurrentUser();
        $token = $this->getTokenService()->makeToken('qrcode', array(
            'userId' => $user['id'],
            'data' => array(
                'url' => $this->generateUrl(
                    'open_course_show',
                    array(
                        'courseId' => $id,
                        'as' => 'preview',
                    ),
                    true),
                'appUrl' => '',
            ),
            'times' => 0,
            'duration' => 3600,
        ));
        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        return $url;
    }

    public function commentAction(Request $request, $courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        if (!$course) {
            return $this->createMessageResponse('error', '课程不存在，或未发布。');
        }

        $conditions = array(
            'targetId' => $course['id'],
            'targetType' => 'openCourse',
            'parentId' => 0,
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchPostsCount($conditions),
            10
        );

        $posts = $this->getThreadService()->searchPosts(
            $conditions,
            array('createdTime' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $isWxPreview = 'preview' === $request->query->get('as') && 'wx' === $request->query->get('previewType');
        if ($isWxPreview || $this->isWxClient()) {
            $template = 'open-course/mobile/open-course-comment.html.twig';
        } else {
            $template = 'open-course/open-course-comment.html.twig';
        }

        return $this->render($template, array(
            'course' => $course,
            'posts' => $posts,
            'users' => $users,
            'paginator' => $paginator,
            'service' => $this->getThreadService(),
            'goto' => $this->generateUrl('open_course_show', array('courseId' => $course['id'])),
        ));
    }

    public function postAction(Request $request, $id)
    {
        if (!$this->_checkCourseStatus($id)) {
            return $this->createMessageResponse('error', '课程不存在，或未发布。');
        }

        return $this->forward('AppBundle:Thread:postSave', array(
            'request' => $request,
            'targetType' => 'openCourse',
            'targetId' => $id,
        ));
    }

    public function postReplyAction(Request $request, $id, $postId)
    {
        if (!$this->_checkCourseStatus($id)) {
            return $this->createMessageResponse('error', '课程不存在，或未发布。');
        }

        $fields = $request->request->all();
        $fields['content'] = $this->autoParagraph($fields['content']);
        $fields['targetId'] = $id;
        $fields['parentId'] = $postId;
        $fields['targetType'] = 'openCourse';

        $post = $this->getThreadService()->createPost($fields);

        return $this->render('thread/subpost-item.html.twig', array(
            'post' => $post,
            'author' => $this->getCurrentUser(),
            'service' => $this->getThreadService(),
        ));
    }

    public function memberSmsAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);
        $user = $this->getCurrentUser();

        if (!$course) {
            return $this->createJsonResponse(array('result' => false, 'message' => '该课程不存在或已删除！'));
        }

        $smsSetting = $this->setting('cloud_sms', array());

        if (!$user->isLogin() && !$smsSetting['sms_enabled']) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        if ('POST' == $request->getMethod()) {
            $member = $this->_memberOperate($request, $id);

            $fields = $request->request->all();
            $fields['isNotified'] = 1;
            $member = $this->getOpenCourseService()->updateMember($member['id'], $fields);

            $this->_loginMemberMobileBind($fields['mobile']);

            $memberNum = $this->getOpenCourseService()->countMembers(array('courseId' => $id, 'isNotified' => 1));

            return $this->createJsonResponse(array('result' => true, 'number' => $memberNum));
        }

        return $this->render('open-course/member-sms-modal.html.twig', array(
            'course' => $course,
        ));
    }

    public function createMemberAction(Request $request, $id)
    {
        $result = $this->_checkExistsMember($request, $id);

        if (!$result['result']) {
            return $this->createJsonResponse($result);
        }

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $fields['ip'] = $request->getClientIp();
            $fields['courseId'] = $id;

            $member = $this->getOpenCourseService()->createMember($fields);
            $memberNum = $this->getOpenCourseService()->countMembers(array('courseId' => $id));

            return $this->createJsonResponse(array('result' => true, 'number' => $memberNum));
        }
    }

    public function playerAction(Request $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            $this->createNewException(OpenCourseException::NOTFOUND_LESSON());
        }

        if ('liveOpen' == $lesson['type'] && 'videoGenerated' == $lesson['replayStatus']) {
            $course = $this->getOpenCourseService()->getCourse($courseId);
            $this->createRefererLog($request, $course);
        }

        return $this->forward('AppBundle:Player:show', array(
            'id' => $lesson['mediaId'],
            'context' => array('hideBeginning' => 1, 'hideQuestion' => 1),
        ));
    }

    public function materialListAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);

        $conditions = array(
            'courseId' => $id,
            'excludeLessonId' => 0,
            'source' => 'opencoursematerial',
            'type' => 'openCourse',
        );

        $materials = $this->getMaterialService()->searchMaterials(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course['id']);
        $lessons = ArrayToolkit::index($lessons, 'id');

        return $this->render('open-course/open-course-material-block.html.twig', array(
            'course' => $course,
            'lessons' => $lessons,
            'materials' => $materials,
        ));
    }

    public function materialDownloadAction(Request $request, $courseId, $materialId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);

        if (empty($material)) {
            $this->createNewException(MaterialException::NOTFOUND_MATERIAL());
        }

        if ('opencourselesson' == $material['source'] || !$material['lessonId']) {
            return $this->createMessageResponse('error', '无权下载该资料');
        }

        return $this->forward('AppBundle:UploadFile:download', array('fileId' => $material['fileId']));
    }

    public function mobileCheckAction(Request $request, $courseId)
    {
        $user = $this->getCurrentUser();
        $response = array('success' => true, 'message' => '');
        $mobile = $request->query->get('value', '');

        $member = $this->getOpenCourseService()->getCourseMemberByMobile($courseId, $mobile);
        if ($member && $member['isNotified']) {
            return $this->createJsonResponse(array('success' => false, 'message' => '该手机号已报名'));
        }

        if ($user->isLogin()) {
            list($result, $message) = $this->getAuthService()->checkMobile($mobile);

            if ('success' != $result) {
                return $this->createJsonResponse(array('success' => false, 'message' => $message));
            }
        }

        return $this->createJsonResponse($response);
    }

    public function adModalRecommendCourseAction(Request $request, $id)
    {
        $num = $request->query->get('num', 3);
        $courseSets = $this->getOpenCourseRecommendedService()->findRandomRecommendCourses($id, $num);
        $courseSets = array_values($courseSets);
        $conditions = array(
            array(
                'status' => 'published',
                'recommended' => 1,
                'parentId' => 0,
            ),
            array(
                'status' => 'published',
                'parentId' => 0,
            ),
        );

        //数量不够 随机取推荐课程里的课程 还是不够随机取所有课程
        foreach ($conditions as $condition) {
            if (count($courseSets) < $num) {
                $needNum = $num - count($courseSets);
                $condition['excludeIds'] = ArrayToolkit::column($courseSets, 'id');
                $randomCourseSets = $this->getCourseSetService()->findRandomCourseSets($condition, $needNum);
                $courseSets = array_merge($courseSets, $randomCourseSets);
            }
        }
        $self = $this;
        $courseSets = array_map(function ($courseSet) use ($self) {
            foreach (array('small', 'middle', 'large') as $coverType) {
                $picturePath = $self->get('web.twig.app_extension')->courseSetCover($courseSet, $coverType);
                $courseSet['cover'][$coverType] = $self->get('web.twig.extension')->getFpath($picturePath, 'course.png');
            }

            return $courseSet;
        }, $courseSets);

        return $this->createJsonResponse($courseSets);
    }

    private function _getMember($courseId)
    {
        $user = $this->getCurrentUser();
        $member = array();

        if ($user->isLogin()) {
            $member = $this->getOpenCourseService()->getCourseMember($courseId, $user['id']);
        } /* else {
        $member = $this->getOpenCourseService()->getCourseMemberByIp($courseId, $user['currentIp']);
        }*/

        return $member;
    }

    private function _getLessonVedioInfo(Request $request, $lesson)
    {
        $lesson['videoWatermarkEmbedded'] = 0;

        if (('video' == $lesson['type'] || ('liveOpen' == $lesson['type'] && 'videoGenerated' == $lesson['replayStatus'])) && 'self' == $lesson['mediaSource']) {
            $file = $this->getUploadFileService()->getFullFile($lesson['mediaId']);

            if ($file) {
                $lesson['convertStatus'] = empty($file['convertStatus']) ? 'none' : $file['convertStatus'];
                $lesson['storage'] = $file['storage'];
            }
        } elseif (in_array($lesson['mediaSource'], array('youku', 'NeteaseOpenCourse', 'qqvideo'))) {
            $proxy = new ParserProxy();
            $lesson = $proxy->prepareMediaUri($lesson);
        }

        if ('liveOpen' == $lesson['type']) {
            if ($lesson['startTime'] > time()) {
                $lesson['startTimeLeft'] = $lesson['startTime'] - time();
            }
        }

        return $lesson;
    }

    private function _checkCourseStatus($courseId)
    {
        $course = $this->getOpenCourseService()->getCourse($courseId);

        if (!$course || ($course && 'published' != $course['status'])) {
            return false;
        }

        return true;
    }

    private function _checkPublishedLessonExists($courseId)
    {
        $lessons = $this->getOpenCourseService()->searchLessons(array(
            'courseId' => $courseId,
            'status' => 'published',
        ),
            array('seq' => 'ASC'), 0, 1
        );

        if (!$lessons) {
            return false;
        }

        return $lessons[0];
    }

    private function _memberOperate(Request $request, $courseId)
    {
        $result = $this->_checkExistsMember($request, $courseId);

        if ($result['result']) {
            $fields = array(
                'courseId' => $courseId,
                'ip' => $request->getClientIp(),
                'lastEnterTime' => time(),
            );
            $member = $this->getOpenCourseService()->createMember($fields);
        } else {
            $member = $this->getOpenCourseService()->updateMember($result['member']['id'], array('lastEnterTime' => time()));
        }

        return $member;
    }

    private function _checkExistsMember(Request $request, $courseId)
    {
        $user = $this->getCurrentUser();
        $userIp = $request->getClientIp();

        if (!$user->isLogin()) {
            $openCourseMember = $this->getOpenCourseService()->getCourseMemberByIp($courseId, $userIp);
        } else {
            $openCourseMember = $this->getOpenCourseService()->getCourseMember($courseId, $user['id']);
            if (!empty($user['verifiedMobile'])) {
                $member = $this->getOpenCourseService()->getCourseMemberByMobile($courseId, $user['verifiedMobile']);
                if ($member) {
                    $openCourseMember = $this->getOpenCourseService()->updateMember($member['id'], array('userId' => $user['id']));
                }
            }
        }

        if ($openCourseMember) {
            return array('result' => false, 'message' => '课程用户已存在！', 'member' => $openCourseMember);
        }

        return array('result' => true);
    }

    protected function autoParagraph($text)
    {
        if ('' !== trim($text)) {
            $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            $text = preg_replace("/\n\n+/", "\n\n", str_replace(array("\r\n", "\r"), "\n", $text));
            $texts = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
            $text = '';

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

        if ('liveOpen' == $lesson['type']) {
            $replays = $this->getLiveReplayService()->searchReplays(array(
                'courseId' => $lesson['courseId'],
                'lessonId' => $lesson['id'],
                'hidden' => 0,
                'type' => 'liveOpen',
            ), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        }

        return $replays;
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

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    /**
     * @return OpenCourseRecommendedService
     */
    protected function getOpenCourseRecommendedService()
    {
        return $this->createService('OpenCourse:OpenCourseRecommendedService');
    }

    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }
}
