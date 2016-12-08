<?php

namespace WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class HomeworkManageController extends BaseController
{
    public function createAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);

        if (empty($course)) {
            throw $this->createResourceNotFoundException('course', $courseId);
        }

        if (empty($lesson)) {
            throw $this->createResourceNotFoundException('lesson', $lessonId);
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $fields['courseId'] = $courseId;
            $fields['lessonId'] = $lessonId;

            $homework = $this->getTestpaperService()->buildTestpaper($fields, 'homework');

            if ($homework) {
                return $this->createJsonResponse(array("status" => "success", 'courseId' => $courseId));
            } else {
                return $this->createJsonResponse(array("status" => "failed"));
            }
        }

        return $this->render('WebBundle:HomeworkManage:create.html.twig', array(
            'course' => $course,
            'lesson' => $lesson
        ));
    }

    public function editAction(Request $request, $courseId, $lessonId, $homeworkId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);

        if (empty($course)) {
            throw $this->createResourceNotFoundException('course', $courseId);
        }

        if (empty($lesson)) {
            throw $this->createResourceNotFoundException('lessonId', $lessonId);
        }

        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if (empty($homework)) {
            throw $this->createResourceNotFoundException('homework', $homeworkId);
        }

        $homeworkItems      = $this->getHomeworkService()->findItemsByHomeworkId($homeworkId);
        $homeworkItemsArray = array();

        foreach ($homeworkItems as $key => $homeworkItem) {
            if ($homeworkItem['parentId'] == "0") {
                $homeworkItemsArray[] = $homeworkItem;
            }
        }

        $homeworkItems = $homeworkItemsArray;
        $questions     = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($homeworkItems, 'questionId'));

        if ($request->getMethod() == 'POST') {
            $fields   = $request->request->all();
            $homework = $this->getHomeworkService()->updateHomework($homeworkId, $fields);

            if ($homework) {
                return $this->createJsonResponse(array("status" => "success", 'courseId' => $courseId));
            } else {
                return $this->createJsonResponse(array("status" => "failed"));
            }
        }

        return $this->render('HomeworkBundle:CourseHomeworkManage:homework-modal.html.twig', array(
            'course'        => $course,
            'lesson'        => $lesson,
            'homework'      => $homework,
            'homeworkItems' => $homeworkItems,
            'questions'     => $questions
        ));
    }

    public function removeAction(Request $request, $courseId, $lessonId, $homeworkId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $result = $this->getHomeworkService()->removeHomework($homeworkId);

        if ($result) {
            return $this->createJsonResponse(array("status" => "success"));
        } else {
            return $this->createJsonResponse(array("status" => "failed"));
        }
    }

    public function homeworkItemsAction(Request $request, $courseId, $homeworkId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if (empty($homework)) {
            throw $this->createResourceNotFoundException('homework', $homeworkId);
        }

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            if (empty($data['questionId']) || empty($data['scores'])) {
                return $this->createMessageResponse('error', '试卷题目不能为空！');
            }

            if (count($data['questionId']) != count($data['scores'])) {
                return $this->createMessageResponse('error', '试卷题目数据不正确');
            }

            $data['questionId'] = array_values($data['questionId']);
            $data['scores']     = array_values($data['scores']);

            $items = array();

            foreach ($data['questionId'] as $index => $questionId) {
                $items[] = array('questionId' => $questionId, 'score' => $data['scores'][$index]);
            }

            $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $items);

            $this->setFlashMessage('success', '试卷题目保存成功！');
            return $this->redirect($this->generateUrl('course_manage_testpaper', array('courseId' => $courseId)));
        }

        $items     = $this->getHomeworkService()->getHomeworkItems($homework['id']);
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));

        $subItems = array();

        foreach ($items as $key => $item) {
            if ($item['parentId'] > 0) {
                $subItems[$item['parentId']][] = $item;
                unset($items[$key]);
            }
        }

        return $this->render('HomeworkBundle:CourseHomeworkManage:homework-items.html.twig', array(
            'course'    => $course,
            'homework'  => $homework,
            'items'     => ArrayToolkit::group($items, 'questionType'),
            'subItems'  => $subItems,
            'questions' => $questions,
            'targets'   => $targets
        ));
    }

    public function questionPickerAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = $request->query->all();

        /*if (empty($conditions['target'])) {
        $conditions['targetPrefix'] = "course-{$courseSet['id']}";
        }*/

        $conditions['parentId'] = 0;

        if (empty($conditions['excludeIds'])) {
            unset($conditions['excludeIds']);
        } else {
            $conditions['excludeIds'] = explode(',', $conditions['excludeIds']);
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = trim($conditions['keyword']);
        }

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchCount($conditions),
            7
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        /*$targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));*/

        return $this->render('WebBundle:HomeworkManage:question-picker.html.twig', array(
            'courseSet'     => $courseSet,
            'questions'     => $questions,
            'replace'       => empty($conditions['replace']) ? '' : $conditions['replace'],
            'paginator'     => $paginator,
            'targetChoices' => $this->getQuestionRanges($courseSet),
            //'targets'       => $targets,
            'conditions'    => $conditions,
            'target'        => $request->query->get('target', 'testpaper')
        ));
    }

    public function pickedQuestionAction(Request $request, $courseSetId, $questionId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $question = $this->getQuestionService()->get($questionId);

        if (empty($question)) {
            throw $this->createResourceNotFoundException('question', $questionId);
        }

        $subQuestions = array();

        return $this->render('WebBundle:HomeworkManage:question-picked-tr.html.twig', array(
            'courseSet'    => $courseSet,
            'question'     => $question,
            'subQuestions' => $subQuestions,
            'type'         => $question['type'],
            'target'       => $request->query->get('target', 'testpaper')
        ));
    }

    public function previewAction(Request $request, $id, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            return $this->createMessageResponse('info', '作业所属课程不存在！');
        }

        $homework = $this->getHomeworkService()->getHomework($id);

        if (empty($homework)) {
            throw $this->createResourceNotFoundException('homework', $homeworkId);
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', '作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);

        $homeworkResult = $this->getHomeworkService()->getResultByHomeworkId($id);

        $user = $this->getUserService()->getUser($homeworkResult['userId']);

        return $this->render('HomeworkBundle:CourseHomeworkManage:preview.html.twig', array(
            'homework'       => $homework,
            'homeworkResult' => $homeworkResult,
            'itemSet'        => $itemSet,
            'course'         => $course,
            'lesson'         => $lesson,
            'user'           => $user,
            'questionStatus' => 'previewing'
        ));
    }

    public function teachingListAction(Request $request)
    {
        $status      = $request->query->get('status', 'reviewing');
        $currentUser = $this->getCurrentUser();

        if (empty($currentUser)) {
            throw $this->createServiceException('用户不存在或者尚未登录，请先登录');
        }

        $courses                = $this->getCourseService()->findUserTeachCourses(array("userId" => $currentUser['id']), 0, PHP_INT_MAX, false);
        $courseIds              = ArrayToolkit::column($courses, 'id');
        $homeworksResultsCounts = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, $status);
        $paginator              = new Paginator(
            $this->get('request'),
            $homeworksResultsCounts
            , 5
        );

        if ($status == 'reviewing') {
            $orderBy = array('usedTime', 'DESC');
        }

        if ($status == 'finished') {
            $orderBy = array('checkedTime', 'DESC');
        }

        $homeworksResults = $this->getHomeworkService()->findResultsByCourseIdsAndStatus(
            $courseIds, $status, $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ($status == 'reviewing') {
            $reviewingCount = $homeworksResultsCounts;
            $finishedCount  = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'finished');
        }

        if ($status == 'finished') {
            $reviewingCount = $this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds, 'reviewing');
            $finishedCount  = $homeworksResultsCounts;
        }

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($homeworksResults, 'courseId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($homeworksResults, 'lessonId'));

        $usersIds = ArrayToolkit::column($homeworksResults, 'userId');
        $users    = $this->getUserService()->findUsersByIds($usersIds);

        return $this->render('HomeworkBundle:CourseHomeworkManage:teaching-list.html.twig', array(
            'status'           => $status,
            'users'            => $users,
            'homeworksResults' => $homeworksResults,
            'paginator'        => $paginator,
            'courses'          => $courses,
            'lessons'          => $lessons,
            'reviewingCount'   => $reviewingCount,
            'finishedCount'    => $finishedCount
        ));
    }

    public function listAction(Request $request)
    {
        $status      = $request->query->get('status', 'finished');
        $currentUser = $this->getCurrentUser();

        $conditions = array(
            'status' => $status,
            'userId' => $currentUser['id']
        );
        $paginator = new Paginator(
            $this->get('request'),
            $this->getHomeworkService()->searchResultsCount($conditions),
            25
        );
        $homeworkResults = $this->getHomeworkService()->searchResults(
            $conditions,
            array('updatedTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $homeworkCourseIds = ArrayToolkit::column($homeworkResults, 'courseId');
        $homeworkLessonIds = ArrayToolkit::column($homeworkResults, 'lessonId');
        $courses           = $this->getCourseService()->findCoursesByIds($homeworkCourseIds);
        $lessons           = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);

        $homeworkResults = $this->_getHomeworkDoTime($homeworkResults);

        return $this->render('HomeworkBundle:CourseHomeworkManage:list.html.twig', array(
            'status'          => $status,
            'homeworkResults' => $homeworkResults,
            'courses'         => $courses,
            'lessons'         => $lessons,
            'user'            => $currentUser,
            'paginator'       => $paginator
        ));
    }

    private function getQuestionRanges($course, $includeCourse = false)
    {
        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $ranges  = array();

        if ($includeCourse == true) {
            $ranges["course-{$course['id']}"] = '本课程';
        }

        foreach ($lessons as $lesson) {
            $ranges["course-{$lesson['courseId']}/lesson-{$lesson['id']}"] = "课时{$lesson['number']}： {$lesson['title']}";
        }

        return $ranges;
    }

    private function sortType($types)
    {
        $newTypes = array('single_choice', 'choice', 'uncertain_choice', 'fill', 'determine', 'essay', 'material');

        foreach ($types as $key => $value) {
            if (!in_array($value, $newTypes)) {
                $k = array_search($value, $newTypes);
                unset($newTypes[$k]);
            }
        }

        return $newTypes;
    }

    private function _getHomeworkDoTime($homeworkResults)
    {
        $homeworkIds     = ArrayToolkit::column($homeworkResults, 'homeworkId');
        $homeworkIdCount = array_count_values($homeworkIds);
        $times           = array();

        foreach ($homeworkResults as $key => $homeworkResult) {
            $homeworkId = $homeworkResult['homeworkId'];

            if (isset($times[$homeworkId])) {
                $times[$homeworkId]--;
            } else {
                $times[$homeworkId] = $homeworkIdCount[$homeworkId];
            }

            $homeworkResults[$key]['time'] = $times[$homeworkId];
        }

        return $homeworkResults;
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMessageService()
    {
        return $this->getServiceKernel()->createService('User.MessageService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
