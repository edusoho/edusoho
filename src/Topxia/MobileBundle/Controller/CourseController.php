<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Form\ReviewType;
use Topxia\Service\Util\CloudClientFactory;

class CourseController extends MobileController
{
    public function __construct()
    {
        $this->setResultStatus();
    }

    public function coursesAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        
        $result = array();
        $result['total'] = $this->getCourseService()->searchCourseCount($conditions);
        $result['start'] = (int) $request->query->get('start', 0);
        $result['limit'] = (int) $request->query->get('limit', 10);
        
        $sort = $request->query->get('sort', 'latest');
        $courses = $this->getCourseService()->searchCourses($conditions, $sort, $result['start'], $result['limit']);

        $result['data'] = $courses = $this->filterCourses($courses);

        return $this->createJson($request, $result);
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

    public function favoriteAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，不能收藏课程！");
        }

        if (!$this->getCourseService()->hasFavoritedCourse($courseId)) {
            $this->getCourseService()->favoriteCourse($courseId);
        }

        return $this->createJson($request, true);
    }

    public function unfavoriteAction(Request $request, $courseId)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，不能收藏课程！");
        }

        if (!$this->getCourseService()->hasFavoritedCourse($courseId)) {
            return $this->createErrorResponse('runtime_error', "您尚未收藏课程，不能取消收藏！");
        }

        $this->getCourseService()->unfavoriteCourse($courseId);

        return $this->createJson($request, true);
    }

    public function meFavoritesAction(Request $request)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，不能收藏课程！");
        }

        $result = array();
        $result['start'] = (int) $request->query->get('start', 0);
        $result['limit'] = (int) $request->query->get('limit', 10);
        $result['total'] = $this->getCourseService()->findUserFavoritedCourseCount($user['id']);

        $courses = $this->getCourseService()->findUserFavoritedCourses($user['id'], $result['start'], $result['limit']);
        $result['data'] = $this->filterCourses($courses);

        return $this->createJson($request, $result);
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
