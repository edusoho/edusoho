<?php

namespace AppBundle\Controller\Course;

use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\MaterialService;
use Biz\Course\Service\ReviewService;
use Biz\File\Service\UploadFileService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\TokenService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseController extends CourseBaseController
{
    public function summaryAction($course, $member = array())
    {
        return $this->render('course/tabs/summary.html.twig', array(
            'course' => $course,
            'member' => $member
        ));
    }

    public function showAction(Request $request, $id, $tab = 'summary')
    {
        $course = $this->getCourseService()->getCourse($id);
        $user   = $this->getCurrentUser();
        return $this->render('course/course-show.html.twig', array(
            'tab' => $tab,
        ));
    }

    public function memberExpiredAction(Request $request, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        if ($member && !$this->getMemberService()->isMemberNonExpired($course, $member)) {
            return $this->render('course/member/expired.html.twig', array(
                'course' => $course
            ));
        }
    }

    public function deadlineReachAction(Request $request, $courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException($this->trans('不允许未登录访问'));
        }

        $this->getMemberService()->quitCourseByDeadlineReach($user['id'], $courseId);

        return $this->redirect($this->generateUrl('course_show', array('id' => $courseId)));
    }

    public function headerAction(Request $request, $course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $courses   = $this->getCourseService()->findPublishedCoursesByCourseSetId($course['courseSetId']);

        $user           = $this->getCurrentUser();
        $member         = $user->isLogin() ? $this->getMemberService()->getCourseMember($course['id'], $user['id']) : array();
        $isUserFavorite = $user->isLogin() ? $this->getCourseSetService()->isUserFavorite($user['id'], $course['courseSetId']) : false;
        $isPreview      = $request->query->get('previewAs', false);

        $previewTasks = $this->getTaskService()->search(array('courseId' => $course['id'], 'type' => 'video', 'isFree' => '1'), array('seq' => 'ASC'), 0, 1);
        return $this->render('course/header/header-for-guest.html.twig', array(
            'isUserFavorite' => $isUserFavorite,
            'member'         => $member,
            'courseSet'      => $courseSet,
            'courses'        => $courses,
            'course'         => $course,
            'previewTask'    => empty($previewTasks) ? null : array_shift($previewTasks),
            'isPreview'      => $isPreview
        ));
    }

    public function notesAction($course, $member = array())
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        if (empty($member)) {
            $notes = $this->getCourseNoteService()->findPublicNotesByCourseSetId($courseSet['id']);
        } else {
            $notes = $this->getCourseNoteService()->findPublicNotesByCourseId($course['id']);
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($notes, 'userId'));
        $users = ArrayToolkit::index($users, 'id');

        $tasks = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($notes, 'taskId'));
        $tasks = ArrayToolkit::index($tasks, 'id');

        $currentUser = $this->getCurrentUser();
        $likes       = $this->getCourseNoteService()->findNoteLikesByUserId($currentUser['id']);
        $likeNoteIds = ArrayToolkit::column($likes, 'noteId');

        return $this->render('course/tabs/notes.html.twig', array(
            'course'      => $course,
            'courseSet'   => $courseSet,
            'notes'       => $notes,
            'users'       => $users,
            'tasks'       => $tasks,
            'likeNoteIds' => $likeNoteIds,
            'member'      => $member
        ));
    }

    public function reviewsAction(Request $request, $course, $member = array())
    {
        $courseSet  = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $conditions = array(
            'parentId'    => 0,
            'courseSetId' => $courseSet['id']
        );

        if (!empty($member)) {
            $conditions['courseId'] = $course['id'];
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getReviewService()->searchReviewsCount($conditions),
            20
        );

        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userReview = array();
        if (!empty($member)) {
            $userReview = $this->getReviewService()->getUserCourseReview($member['userId'], $course['id']);
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('course/tabs/reviews.html.twig', array(
            'courseSet'  => $courseSet,
            'course'     => $course,
            'reviews'    => $reviews,
            'userReview' => $userReview,
            'users'      => $users,
            'member'     => $member
        ));
    }

    public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
    {
        $userIds = array();

        foreach ($courses as $key => $course) {
            $userIds      = array_merge($userIds, $course['teacherIds']);
            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);

            $courses[$key]['classroomCount'] = count($classroomIds);
            if (count($classroomIds) > 0) {
                $classroomId = $classroomIds[0]['classroomId'];
                $classroom   = $this->getClassroomService()->getClassroom($classroomId);

                $courses[$key]['classroom'] = $classroom;
            }
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("course/block/courses-block-{$view}.html.twig", array(
            'courses' => $courses,
            'users'   => $users,
            'mode'    => $mode
        ));
    }

    public function tasksAction($course, $member = array())
    {
        $courseItems = $this->getCourseService()->findCourseItems($course['id']);
        $files       = $this->findFiles($courseItems);
        return $this->render('course/tabs/tasks.html.twig', array(
            'course'      => $course,
            'courseItems' => $courseItems,
            'member'      => $member,
            'files'       => $files
        ));
    }

    public function characteristicAction(Request $request, $course)
    {
        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($course['id']);

        $characteristicData = array();
        $activities         = $this->get('extension.default')->getActivities();
        foreach ($tasks as $task) {
            $type = strtolower($task['activity']['mediaType']);

            if (isset($characteristicData[$type])) {
                $characteristicData[$type]['num']++;
            } else {
                $characteristicData[$type] = array(
                    'icon' => $activities[$type]['meta']['icon'],
                    'name' => $activities[$type]['meta']['name'],
                    'num'  => 1
                );
            }
        }

        return $this->render('course/widgets/characteristic.html.twig', array(
            'course'             => $course,
            'characteristicData' => $characteristicData
        ));
    }

    public function otherCourseAction(Request $request, $course)
    {
        // $this->getCourseService()->getOtherCourses($course['id']);

        return $this->render('course/widgets/other-course.html.twig', array(
            'otherCourse' => $course
        ));
    }

    public function teachersAction(Request $request, $course)
    {
        $teachers = $this->getUserService()->findUsersByIds($course['teacherIds']);

        return $this->render('course/widgets/teachers.html.twig', array(
            'teachers' => $teachers
        ));
    }

    public function newestStudentsAction(Request $request, $course, $member = array())
    {
        $conditions = array(
            'role'   => 'student',
            'locked' => 0
        );

        if (empty($member)) {
            $courses                 = $this->getCourseService()->findCoursesByCourseSetId($course['courseSetId']);
            $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');
        } else {
            $conditions['courseId'] = $course['id'];
        }

        $members    = $this->getMemberService()->searchMembers($conditions, array('createdTime' => 'DESC'), 0, 20);
        $studentIds = ArrayToolkit::column($members, 'userId');
        $students   = $this->getUserService()->findUsersByIds($studentIds);

        return $this->render('course/widgets/newest-students.html.twig', array(
            'students' => $students
        ));
    }

    public function orderInfoAction(Request $request, $sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);

        if (empty($order)) {
            throw $this->createNotFoundException('订单不存在!');
        }

        $course = $this->getCourseService()->getCourse($order['targetId']);

        if (empty($course)) {
            throw $this->createNotFoundException('课程不存在，或已删除。');
        }

        return $this->render('course/widgets/course-order.html.twig', array('order' => $order, 'course' => $course));
    }

    public function qrcodeAction(Request $request, $id)
    {
        $user  = $this->getCurrentUser();
        $host  = $request->getSchemeAndHttpHost();
        $token = $this->getTokenService()->makeToken('qrcode', array(
            'userId'   => $user['id'],
            'data'     => array(
                'url'    => $this->generateUrl('course_show', array('id' => $id), true),
                'appUrl' => "{$host}/mapi_v2/mobile/main#/course/{$id}"
            ),
            'times'    => 1,
            'duration' => 3600
        ));
        $url   = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        $response = array(
            'img' => $this->generateUrl('common_qrcode', array('text' => $url), true)
        );
        return $this->createJsonResponse($response);
    }

    public function exitAction(Request $request, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        $user = $this->getCurrentUser();
        if (empty($member)) {
            throw $this->createAccessDeniedException('您不是课程的学员。');
        }

        if ($member["joinedType"] == "course" && !empty($member['orderId'])) {
            throw $this->createAccessDeniedException('有关联的订单，不能直接退出学习。');
        }

        $this->getCourseMemberService()->removeStudent($course['id'], $user['id']);

        return $this->createJsonResponse(true);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function findFiles($courseItems)
    {
        $activities = ArrayToolkit::column($courseItems, 'activity');

        //获取视频的源数据
        $activityIds = array();
        array_walk($activities, function ($activity) use (&$activityIds) {
            if ($activity['mediaType'] == 'video') {
                array_push($activityIds, $activity['id']);
            }
        });

        $fullActivities = $this->getActivityService()->findActivities($activityIds, $fetchMedia = true);

        $files = array();
        array_walk($fullActivities, function ($activity) use (&$files) {
            $files[$activity['mediaId']] = empty($activity['ext']['file']) ? null : $activity['ext']['file'];
        });
        return $files;
    }
}
