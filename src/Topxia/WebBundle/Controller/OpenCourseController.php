<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseController extends BaseController
{
    public function exploreAction(Request $request, $category)
    {
        $conditions    = $request->query->all();
        $categoryArray = array();
        $levels        = array();

        $conditions['code'] = $category;

        if (!empty($conditions['code'])) {
            $categoryArray             = $this->getCategoryService()->getCategoryByCode($conditions['code']);
            $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($categoryArray['id']);
            $categoryIds               = array_merge($childrenIds, array($categoryArray['id']));
            $conditions['categoryIds'] = $categoryIds;
        }

        unset($conditions['code']);

        if (!isset($conditions['fliter'])) {
            $conditions['fliter'] = array(
                'type'           => 'all',
                'price'          => 'all',
                'currentLevelId' => 'all'
            );
        }

        $fliter = $conditions['fliter'];

        if ($fliter['price'] == 'free') {
            $coinSetting = $this->getSettingService()->get("coin");
            $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1;
            $priceType   = "RMB";

            if ($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
                $priceType = $coinSetting["price_type"];
            }

            if ($priceType == 'RMB') {
                $conditions['price'] = '0.00';
            } else {
                $conditions['coinPrice'] = '0.00';
            }
        }

        if ($fliter['type'] == 'live') {
            $conditions['type'] = 'live';
        }

        if ($this->isPluginInstalled('Vip')) {
            $levels = ArrayToolkit::index($this->getLevelService()->searchLevels(array('enabled' => 1), 0, 100), 'id');

            if ($fliter['currentLevelId'] != 'all') {
                $vipLevelIds               = ArrayToolkit::column($this->getLevelService()->findPrevEnabledLevels($fliter['currentLevelId']), 'id');
                $conditions['vipLevelIds'] = array_merge(array($fliter['currentLevelId']), $vipLevelIds);
            }
        }

        unset($conditions['fliter']);

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!isset($courseSetting['explore_default_orderBy'])) {
            $courseSetting['explore_default_orderBy'] = 'latest';
        }

        $orderBy = $courseSetting['explore_default_orderBy'];
        $orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
        unset($conditions['orderBy']);

        $conditions['parentId'] = 0;
        $conditions['status']   = 'published';
        $paginator              = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions),
            20
        );

        if ($orderBy != 'recommendedSeq') {
            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                $orderBy,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        if ($orderBy == 'recommendedSeq') {
            $conditions['recommended'] = 1;
            $recommendCount            = $this->getCourseService()->searchCourseCount($conditions);
            $currentPage               = $request->query->get('page') ? $request->query->get('page') : 1;
            $recommendPage             = intval($recommendCount / 20);
            $recommendLeft             = $recommendCount % 20;

            if ($currentPage <= $recommendPage) {
                $courses = $this->getCourseService()->searchCourses(
                    $conditions,
                    $orderBy,
                    ($currentPage - 1) * 20,
                    20
                );
            } elseif (($recommendPage + 1) == $currentPage) {
                $courses = $this->getCourseService()->searchCourses(
                    $conditions,
                    $orderBy,
                    ($currentPage - 1) * 20,
                    20
                );
                $conditions['recommended'] = 0;
                $coursesTemp               = $this->getCourseService()->searchCourses(
                    $conditions,
                    'createdTime',
                    0,
                    20 - $recommendLeft
                );
                $courses = array_merge($courses, $coursesTemp);
            } else {
                $conditions['recommended'] = 0;
                $courses                   = $this->getCourseService()->searchCourses(
                    $conditions,
                    'createdTime',
                    (20 - $recommendLeft) + ($currentPage - $recommendPage - 2) * 20,
                    20
                );
            }
        }

        $group = $this->getCategoryService()->getGroupByCode('course');

        if (empty($group)) {
            $categories = array();
        } else {
            $categories = $this->getCategoryService()->getCategoryTree($group['id']);
        }

        if (!$categoryArray) {
            $categoryArrayDescription = array();
        } else {
            $categoryArrayDescription = $categoryArray['description'];
            $categoryArrayDescription = strip_tags($categoryArrayDescription, '');
            $categoryArrayDescription = preg_replace("/ /", "", $categoryArrayDescription);
            $categoryArrayDescription = substr($categoryArrayDescription, 0, 100);
        }

        if (!$categoryArray) {
            $categoryParent = '';
        } else {
            if (!$categoryArray['parentId']) {
                $categoryParent = '';
            } else {
                $categoryParent = $this->getCategoryService()->getCategory($categoryArray['parentId']);
            }
        }

        return $this->render('TopxiaWebBundle:Course:explore.html.twig', array(
            'courses'                  => $courses,
            'category'                 => $category,
            'fliter'                   => $fliter,
            'orderBy'                  => $orderBy,
            'paginator'                => $paginator,
            'categories'               => $categories,
            'consultDisplay'           => true,
            'path'                     => 'course_explore',
            'categoryArray'            => $categoryArray,
            'group'                    => $group,
            'categoryArrayDescription' => $categoryArrayDescription,
            'categoryParent'           => $categoryParent,
            'levels'                   => $levels
        ));
    }

    public function createAction(Request $request)
    {
        $course = $request->request->all();
        unset($course['buyable']);

        $course = $this->getOpenCourseService()->createCourse($course);

        return $this->redirect($this->generateUrl('open_course_manage', array('id' => $course['id'])));
    }

    public function showAction(Request $request, $courseId)
    {
        /*$sms_setting                                  = $this->getSettingService()->get('cloud_sms');
        $sms_setting['sms_open_course_member_notify'] = 'on';
        $this->getSettingService()->set('cloud_sms', $sms_setting);*/

        $course = $this->getOpenCourseService()->getCourse($courseId);

        if (!$this->_checkCourseStatus($courseId)) {
            return $this->createMessageResponse('error', '课程不存在，或未发布。');
        }

        if (!$this->_checkPublishedLessonExists($courseId)) {
            return $this->createMessageResponse('error', '请先创建课时并发布！');
        }

        $this->getOpenCourseService()->waveCourse($courseId, 'hitNum', +1);

        $member = $this->_memberOperate($request, $courseId);

        return $this->render("TopxiaWebBundle:OpenCourse:open-course-show.html.twig", array(
            'course' => $course
        ));
    }

    public function lessonShowAction(Request $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);

        if (!$lesson) {
            return $this->createMessageResponse('error', '该课时不存在！');
        }

        $lesson = $this->_getLessonVedioInfo($request, $lesson);

        return $this->createJsonResponse($lesson);
    }

    /**
     * Block Actions.
     */
    public function headerAction(Request $request, $course)
    {
        $lesson = $this->_checkPublishedLessonExists($course['id']);
        $lesson = $lesson ? $lesson : array();

        $lesson = $this->_getLessonVedioInfo($request, $lesson);

        $member = $this->_getMember($request, $course['id']);

        $lesson['replays'] = $this->_getLiveReplay($lesson);

        return $this->render('TopxiaWebBundle:OpenCourse:open-course-header.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'member' => $member
        ));
    }

    public function teachersAction($courseId)
    {
        $course         = $this->getOpenCourseService()->getCourse($courseId);
        $teachersNoSort = $this->getUserService()->findUsersByIds($course['teacherIds']);

        $teachers = array();

        foreach ($course['teacherIds'] as $key => $teacherId) {
            $teachers[$teacherId] = $teachersNoSort[$teacherId];
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
        $favoriteNum = $this->getOpenCourseService()->favoriteCourse($id);

        return $this->createJsonResponse(array('result' => true, 'number' => $favoriteNum));
    }

    public function unfavoriteAction(Request $request, $id)
    {
        $favoriteNum = $this->getOpenCourseService()->unFavoriteCourse($id);

        return $this->createJsonResponse(array('result' => true, 'number' => $favoriteNum));
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
            array('createdTime' => 'DESC'),
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

        $smsSetting = $this->getSettingService()->get('cloud_sms', array());

        if (!$user->isLogin() && !$smsSetting['sms_enabled']) {
            throw $this->createAccessDeniedException();
        }

        if ($request->getMethod() == 'POST') {
            $member = $this->_memberOperate($request, $courseId);

            $fields = $request->request->all();
            $member = $this->getOpenCourseService()->updateMember($member['id'], $fields);
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

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
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
}
