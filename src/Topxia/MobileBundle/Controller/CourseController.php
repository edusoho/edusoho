<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Common\ArrayToolkit;

class CourseController extends MobileController
{

    public function coursesAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['type'] = 'normal';
        
        $search = $request->query->get('search', '');
        if ($search != '') {
            $conditions['title'] = $search;
        }

        $result = array();
        $result['total'] = $this->getCourseService()->searchCourseCount($conditions);
        $result['start'] = (int) $request->query->get('start', 0);
        $result['limit'] = (int) $request->query->get('limit', 10);
        
        $sort = $request->query->get('sort', 'latest');
        $courses = $this->getCourseService()->searchCourses($conditions, $sort, $result['start'], $result['limit']);

        $result['data'] = $courses = $this->filterCourses($courses);

        return $this->createJson($request, $result);
    }

    public function searchCourses(Request $request)
    {

    }
    
    public function courseAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $error = array('error' => 'not_found', 'message' => "课程#{$courseId}不存在。");
            return $this->createJson($request, $error);
        }

        if ($course['status'] != 'published') {
            $error = array('error' => 'course_not_published', 'message' => "课程#{$courseId}未发布或已关闭。");
        }

        $items = $this->getCourseService()->getCourseItems($courseId);
        $reviews = $this->getReviewService()->findCourseReviews($courseId, 0, 100);
        $learnStatuses = $user->isLogin() ? $this->getCourseService()->getUserLearnLessonStatuses($user['id'], $course['id']) : array();
        $member = $user->isLogin() ? $this->getCourseService()->getCourseMember($course['id'], $user['id']) : null;
        if ($member) {
            $member['createdTime'] = date('c', $member['createdTime']);
        }

        $result = array();
        $result['course'] = $this->filterCourse($course);
        $result['reviews'] = $this->filterReviews($reviews);
        $result['member'] = $member;
        $result['userIsStudent'] = $user->isLogin() ? $this->getCourseService()->isCourseStudent($courseId, $user['id']) : false;
        if (!$result['userIsStudent']){
                $learnStatuses = array();
        }

        $result['userLearns'] = $learnStatuses;
        $result['items'] = $this->filterItems($items);
        foreach ($result['userLearns'] as $lessonId => $status) {
            if (empty($result['items']['lesson-' . $lessonId])) {
                continue;
            }
            $result['items']['lesson-' . $lessonId]['userLearnStatus'] = $status;
        }

        $result['items2'] = array_values($result['items']);

        $result['userFavorited'] = $user->isLogin() ? $this->getCourseService()->hasFavoritedCourse($courseId) : false;

        if ($course) {
            $this->getLogService()->info(MobileController::MOBILE_MODULE, "view_course", "浏览课程",  array(
                "courseId" => $course["id"],
                "title" => $course["title"]
                )
            );
        }
        return $this->createJson($request, $result);
    }

    public function itemsAction(Request $request, $courseId)
    {
        $items = $this->getCourseService()->getCourseItems($courseId);
        $items = $this->filterItems($items);
        return $this->createJson($request, $items);
    }

    public function lessonAction(Request $request, $courseId, $lessonId)
    {

        $token = $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '您尚未登录，不能查看课时！');
        }

        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        $json = array();
        $json['number'] = $lesson['number'];

        $chapter = empty($lesson['chapterId']) ? null : $this->getCourseService()->getChapter($course['id'], $lesson['chapterId']);
        if ($chapter['type'] == 'unit') {
            $unit = $chapter;
            $json['unitNumber'] = $unit['number'];

            $chapter = $this->getCourseService()->getChapter($course['id'], $unit['parentId']);
            $json['chapterNumber'] = empty($chapter) ? 0 : $chapter['number'];

        } else {
            $json['chapterNumber'] = empty($chapter) ? 0 : $chapter['number'];
            $json['unitNumber'] = 0;
        }

        $json['title'] = $lesson['title'];
        $json['summary'] = $lesson['summary'];
        $json['type'] = $lesson['type'];
        $json['content'] = $this->convertAbsoluteUrl($this->container->get('request'), $lesson['content']);
        $json['status'] = $lesson['status'];
        if ($lesson['length'] > 0 and in_array($lesson['type'], array('audio', 'video'))) {
            $json['length'] =  $this->container->get('topxia.twig.web_extension')->durationFilter($lesson['length']);
        } else {
            $json['length'] = 0;
        }
        $json['quizNum'] = $lesson['quizNum'];
        $json['materialNum'] = $lesson['materialNum'];
        $json['mediaId'] = $lesson['mediaId'];
        $json['mediaSource'] = $lesson['mediaSource'];

        if ($json['mediaSource'] == 'self') {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);

            if (!empty($file)) {
                if ($file['storage'] == 'cloud') {
                    $factory = new CloudClientFactory();
                    $client = $factory->createClient();

                    $json['mediaConvertStatus'] = $file['convertStatus'];

                    if (!empty($file['metas2']) && !empty($file['metas2']['hd']['key'])) {

                        if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                            $token = $this->getTokenService()->makeToken('hls.playlist', array('data' => $file['id'], 'times' => 2, 'duration' => 3600));
                            $url = array(
                                'url' => $this->generateUrl('hls_playlist', array(
                                    'id' => $file['id'], 
                                    'token' => $token['token'],
                                    'line' => $request->query->get('line')
                                ), true)
                            );
                        } else {
                            $url = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                        }
                        $json['mediaUri'] = $url['url'];
                    } else {
                        if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                            $key = $file['metas']['hd']['key'];
                        } else {
                            if ($file['type'] == 'video') {
                                $key = null;
                            } else {
                                $key = $file['hashId'];
                            }
                        }

                        if ($key) {
                            $url = $client->generateFileUrl($client->getBucket(), $key, 3600);
                            $json['mediaUri'] = $url['url'];
                        } else {
                            $json['mediaUri'] = '';
                        }

                    }
                } else {
                    $json['mediaUri'] = $this->generateUrl('mapi_course_lesson_media', array('courseId'=>$course['id'], 'lessonId' => $lesson['id'], 'token' => empty($token) ? '' : $token['token']), true);
                }
            } else {
                $json['mediaUri'] = '';
            }
        } else if ($json['mediaSource'] == 'youku') {
            $matched = preg_match('/\/sid\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $json['mediaUri'] = "http://player.youku.com/embed/{$matches[1]}";
            } else {
                $json['mediaUri'] = '';
            }
        } else if ($json['mediaSource'] == 'tudou') {
            $matched = preg_match('/\/v\/(.*?)\/v\.swf/s', $lesson['mediaUri'], $matches);
            if ($matched) {
                $json['mediaUri'] = "http://www.tudou.com/programs/view/html5embed.action?code={$matches[1]}";
            } else {
                $json['mediaUri'] = '';
            }
        } else {
            $json['mediaUri'] = $lesson['mediaUri'];
        }

        $this->getCourseService()->startLearnLesson($courseId, $lessonId);
        return $this->createJson($request, $json);
    }

    public function lessonMediaAction(Request $request, $courseId, $lessonId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录，不能查看课时！");
        }

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);  
        if (empty($lesson) || empty($lesson['mediaId']) || ($lesson['mediaSource'] != 'self') ) {
            throw $this->createNotFoundException();
        }

        if (!$lesson['free']) {
            $this->getCourseService()->tryTakeCourse($courseId);
        }

        return $this->forward('TopxiaWebBundle:CourseLesson:file', array('fileId' => $lesson['mediaId'], 'isDownload' => false));
    }

    /**
     * 收藏课程
     */
    public function favoriteAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录，不能收藏课程！");
        }

        if (!$this->getCourseService()->hasFavoritedCourse($courseId)) {
            $this->getCourseService()->favoriteCourse($courseId);
        }

        return $this->createJson($request, true);
    }

    /**
     * 取消收藏课程
     */
    public function unfavoriteAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录，不能收藏课程！");
        }

        if (!$this->getCourseService()->hasFavoritedCourse($courseId)) {
            return $this->createErrorResponse($request, 'runtime_error', "您尚未收藏课程，不能取消收藏！");
        }

        $this->getCourseService()->unfavoriteCourse($courseId);

        return $this->createJson($request, true);
    }

    public function canLearnAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $result = array('status' => 'fail', 'message' => '您尚未登录，不能学习');
            goto response;
        }

        if (!$this->getCourseService()->isCourseStudent($courseId, $user['id'])) {
            $result = array('status' => 'fail', 'message' => '您不是课程学员，不能学习');
            goto response;
        }

        $result = array('status' => 'ok', 'message' => '');

        response:
        return $this->createJson($request, $result);
    }

    /**
     * 获得当前用户收藏的课程
     */
    public function meFavoritesAction(Request $request)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录，不能收藏课程！");
        }

        $result = array();
        $result['total'] = $this->getCourseService()->findUserFavoritedCourseCount($user['id']);
        $result['start'] = (int) $request->query->get('start', 0);
        $result['limit'] = (int) $request->query->get('limit', 10);

        $courses = $this->getCourseService()->findUserFavoritedCourses($user['id'], $result['start'], $result['limit']);
        $result['data'] = $this->filterCourses($courses);

        return $this->createJson($request, $result);
    }

    /**
     * 获得当前用户正在学和已学完课程
     */
    public function meLearningsAction(Request $request)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录！");
        }

        $result = array();
        $result['total'] = $this->getCourseService()->findUserLeaningCourseCount($user['id']);
        $result['start'] = (int) $request->query->get('start', 0);
        $result['limit'] = (int) $request->query->get('limit', 10);
        $courses = $this->getCourseService()->findUserLeaningCourses($user['id'], $result['start'], $result['limit']);
        $result['data'] = $this->array2Map($this->filterCourses($courses));

        return $this->createJson($request, $result);
    }

    private function array2Map($learnCourses)
    {
        $mapCourses = array();
        if (empty($learnCourses)) {
            return $mapCourses;
        }        
        foreach ($learnCourses as $key => $learnCourse) {
            $mapCourses[$learnCourse['id']] = $learnCourse;
        }

        return $mapCourses;
    }

    /**
     * 获得当前用户已学过的课程
     */
    public function meLearnedsAction(Request $request)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录！");
        }

        $result = array();
        $result['total'] = $this->getCourseService()->findUserLeanedCourseCount($user['id']);
        $result['start'] = (int) $request->query->get('start', 0);
        $result['limit'] = (int) $request->query->get('limit', 10);
        $courses = $this->getCourseService()->findUserLeanedCourses($user['id'], $result['start'], $result['limit']);
        $result['data'] = $this->array2Map($this->filterCourses($courses));

        return $this->createJson($request, $result);
    }

    public function learnAction(Request $request, $courseId, $lessonId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录！");
        }

        $this->getCourseService()->finishLearnLesson($courseId, $lessonId);

        return $this->createJson($request, true);
    }

    public function unlearnAction(Request $request, $courseId, $lessonId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录！");
        }

        $this->getCourseService()->cancelLearnLesson($courseId, $lessonId);

        return $this->createJson($request, true);
    }

    public function learnStatusAction(Request $request, $courseId, $lessonId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', "您尚未登录！");
        }

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'], $courseId, $lessonId);

        return $this->createJson($request, $status ? : 'unstart');
    }

    protected function filterCourse($course)
    {
        if (empty($course)) {
            return null;
        }

        $courses = $this->filterCourses(array($course));

        return current($courses);
    }

    protected function filterCourses($courses)
    {
        if (empty($courses)) {
            return array();
        }

        $teacherIds = array();
        foreach ($courses as $course) {
            $teacherIds = array_merge($teacherIds, $course['teacherIds']);
        }
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $teachers = $this->simplifyUsers($teachers);

        $self = $this;
        $container = $this->container;
        return array_map(function($course) use ($self, $container, $teachers) {
            $course['smallPicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['smallPicture'], 'course-large.png', true);
            $course['middlePicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['middlePicture'], 'course-large.png', true);
            $course['largePicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['largePicture'], 'course-large.png', true);
            $course['about'] = $self->convertAbsoluteUrl($container->get('request'), $course['about']);

            $course['teachers'] = array();
            foreach ($course['teacherIds'] as $teacherId) {
                $course['teachers'][] = $teachers[$teacherId];
            }
            unset($course['teacherIds']);

            return $course;
        }, $courses);
    }

    protected function filterItems($items)
    {
        if (empty($items)) {
            return array();
        }

        $self = $this;
        $container = $this->container;

        return array_map(function($item) use ($self, $container) {
            $item['createdTime'] = date('c', $item['createdTime']);
            if (!empty($item['length']) and in_array($item['type'], array('audio', 'video'))) {
                $item['length'] =  $container->get('topxia.twig.web_extension')->durationFilter($item['length']);
            } else {
                $item['length'] = 0;
            }

            if (empty($item['content'])) {
                $item['content'] = "";
            }
            $item['content'] = $self->convertAbsoluteUrl($container->get('request'), $item['content']);

            return $item;
        }, $items);

    }

    public function convertAbsoluteUrl($request, $html)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function($matches) use ($baseUrl) {
            return "src=\"{$baseUrl}/{$matches[1]}\"";
        }, $html);

        return $html;

    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    private function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    private function getMemberDao ()
    {
        return $this->getServiceKernel()->createDao('Course.CourseMemberDao');
    }
}
