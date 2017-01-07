<?php

namespace AppBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\User\Service\TokenService;
use Biz\Course\Service\ReviewService;
use Biz\Course\Service\MaterialService;
use Biz\Task\Service\TaskResultService;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseNoteService;
use Symfony\Component\HttpFoundation\Request;

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
        return $this->render('course/course-show.html.twig', array(
            'tab' => $tab
        ));
    }

    public function headerAction(Request $request, $course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $courses   = $this->getCourseService()->findPublishedCoursesByCourseSetId($course['courseSetId']);

        $user           = $this->getCurrentUser();
        $member         = $user->isLogin() ? $this->getMemberService()->getCourseMember($course['id'], $user['id']) : array();
        $isUserFavorite = $user->isLogin() ? $this->getCourseSetService()->isUserFavorite($user['id'], $course['courseSetId']) : false;

        return $this->render('course/header/header-for-guest.html.twig', array(
            'isUserFavorite' => $isUserFavorite,
            'member'         => $member,
            'courseSet'      => $courseSet,
            'courses'        => $courses,
            'course'         => $course
        ));
    }

    public function notesAction($course, $member = array())
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $notes     = $this->getCourseNoteService()->findPublicNotesByCourseId($course['id']);

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
            'courseId' => $course['id'],
            'parentId' => 0
        );

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
            //TODO
            // $userIds = array_merge($userIds, $course['teacherIds']);

            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($course['id']);

            $courses[$key]['classroomCount'] = count($classroomIds);
            $courses[$key]['courseSet']      = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            if (count($classroomIds) > 0) {
                $classroom                  = $this->getClassroomService()->getClassroom($classroomIds[0]);
                $courses[$key]['classroom'] = $classroom;
            }
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("course/courses-block-{$view}.html.twig", array(
            'courses' => $courses,
            'users'   => $users,
            //'classroomIds' => $classroomIds,
            'mode'    => $mode
        ));
    }

    public function tasksAction($course, $member = array())
    {
        $courseItems = $this->getCourseService()->findCourseItems($course['id']);

        return $this->render('course/tabs/tasks.html.twig', array(
            'course'      => $course,
            'courseItems' => $courseItems,
            'member'      => $member
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
            'otherCourse' => $course,
        ));
    }

    public function teachersAction(Request $request, $course)
    {
        $teachers = $this->getUserService()->findUsersByIds($course['teacherIds']);

        return $this->render('course/widgets/teachers.html.twig', array(
            'teachers' => $teachers
        ));
    }

    public function newestStudentsAction(Request $request, $course)
    {
        $conditions = array(
            'courseId' => $course['id'],
            'role'     => 'student',
            'locked'   => 0
        );

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
        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        $response = array(
            'img' => $this->generateUrl('common_qrcode', array('text' => $url), true)
        );
        return $this->createJsonResponse($response);
    }

    public function exitAction(Request $request, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);
        $user                  = $this->getCurrentUser();
        if (empty($member)) {
            throw $this->createAccessDeniedException('您不是课程的学员。');
        }

        if ($member["joinedType"] == "course" && !empty($member['orderId'])) {
            throw $this->createAccessDeniedException('有关联的订单，不能直接退出学习。');
        }

        $this->getCourseMemberService()->removeStudent($course['id'], $user['id']);

        return $this->createJsonResponse(true);
    }

    // TODO old
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Note:CourseNoteService');
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
}
