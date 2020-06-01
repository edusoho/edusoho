<?php

namespace AppBundle\Controller\EsBar;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class EsBarController extends BaseController
{
    public function studyCenterAction(Request $request)
    {
        return $this->render('es-bar/list-content/study-center.html.twig');
    }

    public function courseAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $conditions = [
            'userId' => $user->id,
            'locked' => 0,
            'classroomId' => 0,
            'role' => 'student',
        ];
        $sort = ['createdTime' => 'DESC'];
        $members = $this->getCourseMemberService()->searchMembers($conditions, $sort, 0, 15);
        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courseConditions = [
            'courseIds' => $courseIds,
            'parentId' => 0,
        ];
        $courses = $this->getCourseService()->searchCourses($courseConditions, 'default', 0, 15);
        $courses = ArrayToolkit::index($courses, 'id');
        $sortedCourses = [];

        if (!empty($courses)) {
            foreach ($members as $member) {
                if (empty($courses[$member['courseId']])) {
                    continue;
                }

                $course = $courses[$member['courseId']];

                if (0 != $course['taskNum']) {
                    $course['percent'] = intval($member['learnedNum'] / $course['taskNum'] * 100);
                } else {
                    $course['percent'] = 0;
                }

                $sortedCourses[] = $course;
            }
        }

        return $this->render(
            'es-bar/list-content/study-place/my-course.html.twig',
            [
                'courses' => $sortedCourses,
            ]
        );
    }

    public function classroomAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $memberConditions = [
            'userId' => $user->id,
            'locked' => 0,
            'role' => 'student',
        ];
        $sort = ['createdTime' => 'DESC'];

        $members = $this->getClassroomService()->searchMembers($memberConditions, $sort, 0, 15);

        $classroomIds = ArrayToolkit::column($members, 'classroomId');
        $classrooms = [];
        $sortedClassrooms = [];

        if (!empty($classroomIds)) {
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
        }

        foreach ($members as $member) {
            if (empty($classrooms[$member['classroomId']])) {
                continue;
            }

            $classroom = $classrooms[$member['classroomId']];

            $sortedClassrooms[] = $classroom;
        }

        return $this->render(
            'es-bar/list-content/study-place/my-classroom.html.twig',
            [
                'classrooms' => $sortedClassrooms,
            ]
        );
    }

    public function notifyAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $notifications = $this->getNotificationService()->searchNotificationsByUserId($user->id, 0, 15);
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);

        return $this->render(
            'es-bar/list-content/notification/notify.html.twig',
            [
                'notifications' => $notifications,
            ]
        );
    }

    public function practiceAction(Request $request, $status)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        return $this->render(
            'es-bar/list-content/practice/practice.html.twig',
            [
                'testpaperData' => $this->getTestpaperData($request, $status),
                'homeworkData' => $this->getHomeworkData($request, $status),
                'status' => $status,
            ]
        );
    }

    protected function getTestpaperData($request, $status)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！', '', 5, $this->generateUrl('login'));
        }

        $courseIds = ArrayToolkit::column(
            $this->getCourseMemberService()->searchMembers(['userId' => $user['id']], [], 0, PHP_INT_MAX),
            'courseId'
        );
        if (empty($courseIds)) {
            return [
                'answerRecords' => [],
            ];
        }
        $activities = ArrayToolkit::index(
            $this->getActivityService()->search(['courseIds' => $courseIds, 'mediaType' => 'testpaper'], [], 0, PHP_INT_MAX),
            'mediaId'
        );
        $testpeaperActivities = ArrayToolkit::index(
            $this->getTestpaperActivityService()->findActivitiesByIds(array_keys($activities)),
            'answerSceneId'
        );
        if (empty(array_keys($testpeaperActivities))) {
            return [
                'answerRecords' => [],
            ];
        }

        $conditions = [
            'answer_scene_ids' => array_keys($testpeaperActivities),
            'user_id' => $user['id'],
            'status' => $status,
        ];

        $paginator = new Paginator(
            $request,
            $this->getAnswerRecordService()->count($conditions),
            10
        );

        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            ['begin_time' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (empty($answerRecords)) {
            return [
                'answerRecords' => [],
            ];
        }

        $tasks = ArrayToolkit::index(
            $this->getTaskService()->findTasksByActivityIds(ArrayToolkit::column($activities, 'id')),
            'activityId'
        );
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($answerRecords, 'assessment_id'));

        return [
            'testpeaperActivities' => $testpeaperActivities,
            'answerRecords' => $answerRecords,
            'tasks' => $tasks,
            'assessments' => $assessments,
            'courses' => $courses,
            'activities' => $activities,
            'pageCount' => ceil($paginator->getItemCount() / $paginator->getPerPageCount()),
        ];
    }

    protected function getHomeworkData($request, $status)
    {
        $user = $this->getCurrentUser();

        $courseIds = ArrayToolkit::column(
            $this->getCourseMemberService()->searchMembers(['userId' => $user['id']], [], 0, PHP_INT_MAX),
            'courseId'
        );
        if (empty($courseIds)) {
            return [
                'answerRecords' => [],
            ];
        }
        $activities = ArrayToolkit::index(
            $this->getActivityService()->search(['courseIds' => $courseIds, 'mediaType' => 'homework'], [], 0, PHP_INT_MAX),
            'mediaId'
        );
        $homeworkActivities = ArrayToolkit::index(
            $this->getHomeworkActivityService()->findByIds(array_keys($activities)),
            'answerSceneId'
        );
        if (empty(array_keys($homeworkActivities))) {
            return [
                'answerRecords' => [],
            ];
        }

        $conditions = [
            'answer_scene_ids' => array_keys($homeworkActivities),
            'user_id' => $user['id'],
            'status' => $status,
        ];

        $paginator = new Paginator(
            $request,
            $this->getAnswerRecordService()->count($conditions),
            10
        );

        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            ['begin_time' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (empty($answerRecords)) {
            return [
                'answerRecords' => [],
            ];
        }

        $tasks = ArrayToolkit::index(
            $this->getTaskService()->findTasksByActivityIds(ArrayToolkit::column($activities, 'id')),
            'activityId'
        );
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($answerRecords, 'assessment_id'));

        return [
            'homeworkActivities' => $homeworkActivities,
            'answerRecords' => $answerRecords,
            'tasks' => $tasks,
            'assessments' => $assessments,
            'courses' => $courses,
            'activities' => $activities,
            'pageCount' => ceil($paginator->getItemCount() / $paginator->getPerPageCount()),
        ];
    }

    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }

    protected function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }
}
