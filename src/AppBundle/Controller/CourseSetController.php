<?php

namespace AppBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Note\Service\CourseNoteService;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        list($courseSet, $course) = $this->getCourseSetAndCourse($request, $id);
        return $this->render('course-set/overview.html.twig', array(
            'courseSet' => $courseSet,
            'course'    => $course
        ));
    }

    public function notesAction(Request $request, $id)
    {
        list($courseSet, $course) = $this->getCourseSetAndCourse($request, $id);

        if (empty($courseSet)) {
            throw $this->createNotFoundException('找不到该课程');
        }

        $notes = $this->getCourseNoteService()->findPublicNotesByCourseSetId($courseSet['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($notes, 'userId'));
        $users = ArrayToolkit::index($users, 'id');

        $tasks = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($notes, 'taskId'));
        $tasks = ArrayToolkit::index($tasks, 'id');

        $currentUser = $this->getCurrentUser();
        $likes       = $this->getCourseNoteService()->findNoteLikesByUserId($currentUser['id']);
        $likeNoteIds = ArrayToolkit::column($likes, 'noteId');
        return $this->render('course-set/note/notes.html.twig', array(
            'course'      => $course,
            'courseSet'   => $courseSet,
            'notes'       => $notes,
            'users'       => $users,
            'tasks'       => $tasks,
            'likeNoteIds' => $likeNoteIds
        ));
    }

    public function reviewListAction(Request $request, $id)
    {
        list($courseSet, $course) = $this->getCourseSetAndCourse($request, $id);
        list($course, $member)    = $this->getCourseService()->tryTakeCourse($course['id']);

        $courseId = $request->query->get('courseId', 0);

        $conditions = array(
            'courseSetId' => $courseSet['id'],
            'parentId'    => 0
        );

        if ($courseId > 0) {
            $conditions['courseId'] = $courseId;
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

        $user       = $this->getCurrentUser();
        $userReview = $this->getReviewService()->getUserCourseReview($user['id'], $course['id']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));

        return $this->render('course-set/review/list.html.twig', array(
            'courseSet'  => $courseSet,
            'course'     => $course,
            'reviews'    => $reviews,
            'userReview' => $userReview,
            'users'      => $users,
            'member'     => $member
        ));
    }

    protected function getCourseSetAndCourse(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        $courseId = $request->query->get('courseId', 0);

        if ($courseId > 0) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($id);
        }

        return array($courseSet, $course);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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

    /*
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
