<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Activity\Service\ActivityService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity, $preview = 0)
    {
        if ($preview) {
            return $this->previewExercise($activity['id'], $activity['fromCourseId']);
        }

        $user = $this->getUser();

        $activity = $this->getActivityService()->getActivity($activity['id']);
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);
        $exerciseResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $exercise['id'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        if (!$exerciseResult || ($exerciseResult['status'] == 'doing' && !$exerciseResult['updateTime'])) {
            return $this->render('activity/exercise/show.html.twig', array(
                'activity' => $activity,
                'exerciseResult' => $exerciseResult,
                'exercise' => $exercise,
                'courseId' => $activity['fromCourseId'],
            ));
        }

        return $this->forward('AppBundle:Exercise:startDo', array(
            'lessonId' => $activity['id'],
            'exerciseId' => $activity['mediaId'],
        ));
    }

    public function previewAction(Request $request, $task)
    {
        return $this->previewExercise($task['activityId'], $task['courseId']);
    }

    protected function previewExercise($id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);

        if (!$exercise) {
            return $this->createMessageResponse('error', 'exercise not found');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($exercise['id']);
        $attachments = $this->getTestpaperService()->findAttachments($exercise['id']);

        $exercise['itemCount'] = $this->getActureQuestionNum($questions);

        return $this->render('activity/exercise/preview.html.twig', array(
            'paper' => $exercise,
            'questions' => $questions,
            'paperResult' => array(),
            'activity' => $activity,
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $course = $this->getCourseService()->getCourse($courseId);
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);
        unset($exercise['id']);
        $activity = array_merge($activity, $exercise);

        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes(array('courseSetId' => $course['courseSetId']));
        $questionNums = ArrayToolkit::index($questionNums, 'type');

        $questionNums['material']['questionNum'] = $this->getQuestionService()->searchCount(array('type' => 'material', 'subCount' => 0, 'courseSetId' => $course['courseSetId']));

        $user = $this->getUser();

        $range = $this->parseRange($activity);
        $courseTasks = $this->findCourseTasksByCourseId($range['courseId']);

        return $this->render('activity/exercise/modal.html.twig', array(
            'questionNums' => $questionNums,
            'activity' => $activity,
            'courseSetId' => $course['courseSetId'],
            'course' => $course,
            'courseTasks' => $courseTasks,
            'range' => $range,
            'courseId' => $course['id'],
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes(array('courseSetId' => $course['courseSetId']));
        $questionNums = ArrayToolkit::index($questionNums, 'type');

        $questionNums['material']['questionNum'] = $this->getQuestionService()->searchCount(array('type' => 'material', 'subCount' => 0, 'courseSetId' => $course['courseSetId']));

        $user = $this->getUser();

        return $this->render('activity/exercise/modal.html.twig', array(
            'courseId' => $courseId,
            'questionNums' => $questionNums,
            'courseSetId' => $course['courseSetId'],
            'course' => $course,
        ));
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);

        return $this->render('activity/exercise/finish-condition.html.twig', array(
            'exercise' => $exercise,
        ));
    }

    protected function findCourseTestpapers($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'status' => 'open',
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $testpapers;
    }

    protected function parseRange($activity)
    {
        $rangeDefault = array('courseId' => 0);
        $range = empty($activity['metas']['range']) ? $rangeDefault : $activity['metas']['range'];

        if (is_array($range)) {
            return $range;
        } elseif ($range == 'course') {
            return $rangeDefault;
        } elseif ($range == 'lesson') {
            //兼容老数据
            $conditions = array(
                'activityId' => $activity['id'],
                'type' => 'exercise',
                'courseId' => $activity['fromCourseId'],
            );
            $task = $this->getCourseTaskService()->searchTasks($conditions, null, 0, 1);

            if (!$task) {
                return $rangeDefault;
            }

            $conditions = array(
                'categoryId' => $task[0]['categoryId'],
                'mode' => 'lesson',
            );
            $lessonTask = $this->getCourseTaskService()->searchTasks($conditions, null, 0, 1);
            if ($lessonTask) {
                return array('courseId' => $lessonTask[0]['courseId'], 'lessonId' => $lessonTask[0]['id']);
            }

            return $rangeDefault;
        }

        return $targetDefault;
    }

    protected function getActureQuestionNum($questions)
    {
        $count = 0;
        array_map(function ($question) use (&$count) {
            if ($question['type'] == 'material') {
                $count += count($question['subs']);
            } else {
                $count += 1;
            }
        }, $questions);

        return $count;
    }

    protected function findCourseTasksByCourseId($courseId)
    {
        if (empty($courseId)) {
            return array();
        }

        $conditions = array(
            'courseId' => $courseId,
            'typesNotIn' => array('testpaper', 'homework', 'exercise'),
        );

        return $this->getTaskService()->searchTasks($conditions, array(), 0, PHP_INT_MAX);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
