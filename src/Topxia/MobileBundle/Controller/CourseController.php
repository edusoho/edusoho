<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Util\CloudClientFactory;

class CustomSort
{
    static function sortChapters($a, $b)
    {
        return $a['number'] > $b['number'];
    }
}

class CourseController extends MobileController
{
    public function __construct()
    {
        $this->setResultStatus();
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
        $result['items'] = $this->filterItems($items);
        $result['reviews'] = $this->filterReviews($reviews);
        $result['member'] = $member;
        $result['userIsStudent'] = $user->isLogin() ? $this->getCourseService()->isCourseStudent($courseId, $user['id']) : false;
        $result['userLearns'] = $learnStatuses;
        $result['userFavorited'] = $user->isLogin() ? $this->getCourseService()->hasFavoritedCourse($courseId) : false;

        return $this->createJson($request, $result);
    }

    public function itemsAction(Request $request, $courseId)
    {
        $items = $this->getCourseService()->getCourseItems($courseId);
        return $this->createJson($request, $items);
    }

    public function lessonAction(Request $request, $courseId, $lessonId)
    {
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
        $json['content'] = $lesson['content'];
        $json['status'] = $lesson['status'];
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
                        $url = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
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
                    $json['mediaUri'] = $this->generateUrl('course_lesson_media', array('courseId'=>$course['id'], 'lessonId' => $lesson['id']));
                }
            } else {
                $json['mediaUri'] = '';
            }
        } else {
            $json['mediaUri'] = $lesson['mediaUri'];
        }

        return $this->createJson($request, $json);
    }

    public function getCommentAction(Request $request, $courseId)
    {
        $token = $this->getUserToken($request);
        $course_comment = $this->getReviewService()->findCourseReviews($courseId, 0, 12);
        $course_comment = $this->changeCreatedTime($course_comment);
        $commentUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($course_comment, 'userId'));
        $commentUsers = $this->changeUserPicture($commentUsers, true);

        $course_comment = $this->subTime($course_comment);
        $this->result['course_comment'] = $course_comment;
        $this->result['commentUsers'] = $commentUsers;
        $this->setResultStatus("success");
        return $this->createJson($request, $this->result);
    }

    protected function subTime($course_comments)
    {
        for($i=0; $i < count($course_comments); $i++) {
            $course_comments[$i]['createdTime'] =  substr($course_comments[$i]['createdTime'], 0, 10);
        }
        return $course_comments;
    }

    public function commentCourseAction(Request $request, $id)
    {
        $token = $this->getUserToken($request);
        if ($token) {
            $currentUser = $this->getCurrentUser();
            $course = $this->getCourseService()->getCourse($id);
            $review = $this->getReviewService()->getUserCourseReview($currentUser['id'], $course['id']);
            $form = $request->query->all();
            $form['rating'] = $form['rating'];
            $form['userId']= $currentUser['id'];
            $form['courseId']= $id;
            $this->getReviewService()->saveReview($form);
            $this->setResultStatus("success");
            return $this->createJson($request, $this->result);
        }
        return $this->createJson($request, $this->result);
    }

    public function getfavoriteCourseAction(Request $request)
    {
        $token = $this->getUserToken($request);
        if ($token) {
            $page = $this->getParam($request, 'page', 0);
            $favoriteCourses = $this->getCourseService()->findUserFavoritedCourses($token['userId'], $page, self::$defLimit);
            $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($favoriteCourses, 'userId'));
            $this->setResultStatus("success");
            $this->result['users'] = $users;
            $this->result['favoriteCourses'] = $this->changeCoursePicture($favoriteCourses, true);
            $count = $this->getCourseService()->findUserFavoritedCourseCount($token['userId']);
            $this->result = $this->setPage($this->result, $page, $count);
        }
        return $this->createJson($request, $this->result);
    }

    public function favoriteAction(Request $request)
    {
        $token = $this->getUserToken($request);
        if ($token) {
            $course_id = $this->getParam($request, 'course_id');
            if ($this->getCourseService()->hasFavoritedCourse($course_id)) {
                $this->result['message'] = "课程已收藏";
            } else if ($this->getCourseService()->favoriteCourse($course_id)) {
                $this->setResultStatus("success");
            }
        }
    
        return $this->createJson($request, $this->result);
    }

    public function learnStatusAction(Request $request, $courseId, $lessonId)
    {
        $token = $this->getUserToken($request);
        if ($token) {
            $user = $this->getCurrentUser();
            $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user['id']);
            if (empty($member) or !in_array($member['role'], array('admin', 'teacher', 'student'))) {
                $status = "unstart";
            } else {
                $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'], $courseId, $lessonId);
            }
        }
        return $this->createJson($request, $status ? : 'unstart');
    }

    public function learnCancelAction(Request $request, $courseId, $lessonId)
    {
        $token = $this->getUserToken($request);
        if ($token) {
            $user = $this->getCurrentUser();
            $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user['id']);
            if (empty($member) or !in_array($member['role'], array('admin', 'teacher', 'student'))) {
                $this->setResultStatus("error");
                $this->result["message"] = "您不是课程学员，不能学习！";
            } else {
                $this->getCourseService()->cancelLearnLesson($courseId, $lessonId);
                $this->setResultStatus("success");
            }
            
        }
        return $this->createJson($request, $this->result);
    }

    public function learnFinishAction(Request $request, $courseId, $lessonId)
    {
        $token = $this->getUserToken($request);
        $user = $this->getCurrentUser();
        $member = $this->getMemberDao()->getMemberByCourseIdAndUserId($courseId, $user['id']);
        if (empty($member) or !in_array($member['role'], array('admin', 'teacher', 'student'))) {
            $this->setResultStatus("error");
            $this->result["message"] = "您不是课程学员，不能学习！";
        } else {
            $this->getCourseService()->finishLearnLesson($courseId, $lessonId);
            $member = $this->getCourseService()->getCourseMember($courseId, $user['id']);

            $this->setResultStatus("success");
            $this->result['result'] = array(
                'learnedNum' => empty($member['learnedNum']) ? 0 : $member['learnedNum'],
                'isLearned' => empty($member['isLearned']) ? 0 : $member['isLearned'],
            );
        }
        
        return $this->createJson($request, $this->result);
    }

    public function unFavoriteAction(Request $request)
    {
        $token = $this->getUserToken($request);
        if ($token) {
            $course_id = $this->getParam($request, 'course_id', 0);
            if ($this->getCourseService()->hasFavoritedCourse($course_id)) {
                if ($this->getCourseService()->unFavoriteCourse($course_id)) {
                   $this->setResultStatus("success");
                }  
            }
        }
        
        return $this->createJson($request, $this->result);
    }

    public function getLearnCourseAction(Request $request)
    {   
        $token = $this->getUserToken($request);
        if ($token) {
            $page = $this->getParam($request, 'page', 0);
            $count = $this->getCourseService()->findUserLeaningCourseCount($token['userId']);
            $learnCourses = $this->getCourseService()->findUserLeaningCourses($token['userId'], $page, self::$defLimit);
            $learnCourses = $this->changeLearnCourse($learnCourses);
            $this->setResultStatus("success");
            $this->result['learnCourses'] = $learnCourses;
            $this->result = $this->setPage($this->result, $page, $count);
        }
        return $this->createJson($request, $this->result);
    }

    public function getLearnedCourseAction(Request $request)
    {
        $token = $this->getUserToken($request);
        if ($token) {
            $page = $this->getParam($request, 'page', 0);
            $count = $this->getCourseService()->findUserLeanedCourseCount($token['userId']);
            $learnCourses = $this->getCourseService()->findUserLeanedCourses($token['userId'], $page, self::$defLimit);
            $learnCourses = $this->changeLearnCourse($learnCourses);
            $this->setResultStatus("success");
            $this->result['learnedCourses'] = $learnCourses;
            $this->result = $this->setPage($this->result, $page, $count);
        }
        return $this->createJson($request, $this->result);
    }

    protected function changeLearnCourse($learnCourses)
    {
        $keys = array_keys($learnCourses);
        foreach($keys as $i) {
            $learnCourses[$i] = $this->_changeCoursePicture($learnCourses[$i]);
        }
        return $learnCourses;
    }

    public function getCourseLessonAction(Request $request, $course_id)
    {
        $courseLesson = $this->getCourseService()->getCourseItems($course_id);
        $rootChapters = array(
            "id"=>"0",
            "number"=>0,
            "createdTime"=>"",
            "seq"=>"0",
            "courseId"=>$course_id,
            "title"=>"课时列表"
        );
        $chapters = $this->getCourseService()->getCourseChapters($course_id);
        array_unshift($chapters, $rootChapters);

        $sort = new CustomSort();
        usort($chapters, array($sort,'sortChapters'));

        for ($i=0; $i<count($chapters); $i++) {
            $chapters[$i]['course_lesson_list'] = $this->_findLessonByChapterId($courseLesson, $chapters[$i]['id']);
        }
        
        $hls = array();
        foreach ($courseLesson as $lesson) {
            if (! isset($lessoon["type"]) || ! isset($lesson['mediaSource'])) {
                continue;
            }
            if ($lesson['type'] == 'video' and $lesson['mediaSource'] == 'self') {
                $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
                if (!empty($file['metas2']) && !empty($file['metas2']['hd']['key'])) {
                    $factory = new CloudClientFactory();
                    $client = $factory->createClient();
                    $hls[$lesson["id"]] = $client->generateHLSQualitiyListUrl($file['metas2'], 3600);
                }
            }
        }
        
        $result = array(
            "chapters"=>$chapters,
            "hls"=>$hls
        );
        return $this->createJson($request, $result);
    }

    protected function _findLessonByChapterId($courseLesson, $chapterId)
    {
        $temp_lessons = array();
        foreach($courseLesson as $lesson) {
            if (isset($lesson['chapterId']) && $lesson['chapterId'] == $chapterId) {
                array_push($temp_lessons, $lesson);
            }
        }
        return $temp_lessons;
    }

    /**
    * sort => latest, popular, recommended, Rating, hitNum, studentNum, createdTime
    */
    public function getCourseListAction(Request $request)
    {
        $conditions = array('status' => 'published');
        $search = $request->query->get('search');
        if ($search) {
            $conditions['title'] = $search;
        }

        $sort = $request->query->get('sort');
        if (empty($sort)) {
            $sort = "latest";
        }

        $page = $request->query->get('page');
        if (empty($page)) {
            $page = 0;
        }

        $count = $this->getCourseService()->searchCourseCount($conditions);
        $courses = $this->getCourseService()->searchCourses($conditions, $sort, $page * self::$defLimit, self::$defLimit);
        $courses = $this->changeCoursePicture($courses, true);

        $result = $this->coursesBlock($courses);
        $result = $this->setPage($result, $page, $count);

        return $this->createJson($request, $result);
    }

    protected function coursesBlock($courses, $mode = 'default')
    {
        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = $this->changeUserPicture($users, true);

        return array(
            'courses' => $courses,
            'users' => $users,
            'mode' => $mode,
        );
    }

    private function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payNames = array('alipay');
        foreach ($payNames as $payName) {
            if (!empty($setting[$payName . '_enabled'])) {
                $enableds[$payName] = array(
                    'type' => empty($setting[$payName . '_type']) ? '' : $setting[$payName . '_type'],
                );
            }
        }

        return $enableds;
    }

    public function simpleUser($user) 
    {
        if (empty($user)) {
            return null;
        }
        $users = $this->simplifyUsers(array($user));
        return current($users);
    }

    public function simplifyUsers($users)
    {
        if (empty($users)) {
            return array();
        }

        $simplifyUsers = array();
        foreach ($users as $key => $user) {
            $simplifyUsers[$key] = array (
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'title' => $user['title'],
                'avatar' => $this->container->get('topxia.twig.web_extension')->getFilePath($user['smallAvatar'], 'avatar.png', true),
            );
        }

        return $simplifyUsers;
    }

    protected function filterReviews($reviews)
    {
        if (empty($reviews)) {
            return array();
        }

        $userIds = ArrayToolkit::column($reviews, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $self = $this;
        return array_map(function($review) use ($self, $users) {
            $review['user'] = empty($users[$review['userId']]) ? null : $self->simpleUser($users[$review['userId']]);
            unset($review['userId']);

            $review['createdTime'] = date('c', $review['createdTime']);
            return $review;
        }, $reviews);

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

        $teacherIdss = ArrayToolkit::column($courses, 'teacherIds');
        $teacherIds = array();
        foreach ($teacherIdss as $ids) {
            $teacherIds = array_merge($teacherIds, $ids);
        }

        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $teachers = $this->simplifyUsers($teachers);

        $self = $this;
        $container = $this->container;
        return array_map(function($course) use ($self, $container, $teachers) {
            $course['smallPicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['smallPicture'], 'course-large.png', true);
            $course['middlePicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['middlePicture'], 'course-large.png', true);
            $course['largePicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['largePicture'], 'course-large.png', true);

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

        return array_map(function($item) {
            $item['createdTime'] = date('c', $item['createdTime']);
            return $item;
        }, $items);

    }

    private function canShowCourse($course, $user)
    {
        return ($course['status'] == 'published') or 
            $user->isAdmin() or 
            $this->getCourseService()->isCourseTeacher($course['id'],$user['id']) or
            $this->getCourseService()->isCourseStudent($course['id'],$user['id']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
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
