<?php

namespace AppBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class HomeworkManageController extends BaseController
{
    public function questionPickerAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = $request->query->all();

        $conditions['parentId'] = 0;

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

        return $this->render('homework/manage/question-picker.html.twig', array(
            'courseSet'     => $courseSet,
            'questions'     => $questions,
            'replace'       => empty($conditions['replace']) ? '' : $conditions['replace'],
            'paginator'     => $paginator,
            'targetChoices' => $this->getQuestionRanges($courseSet['id']),
            'conditions'    => $conditions,
            'target'        => $request->query->get('target', 'testpaper')
        ));
    }

    public function pickedQuestionAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $questionIds = $request->query->get('questionIds', array(0));
        $questions   = $this->getQuestionService()->findQuestionsByIds($questionIds);

        foreach ($questions as &$question) {
            if ($question['subCount'] > 0) {
                $question['subs'] = $this->getQuestionService()->findQuestionsByParentId($question['id']);
            }
        }

        return $this->render('homework/manage/question-picked.html.twig', array(
            'courseSet'     => $courseSet,
            'questions'     => $questions,
            'type'          => $question['type'],
            'target'        => $request->query->get('target', 'testpaper'),
            'targetChoices' => $this->getQuestionRanges($courseSet['id'])
        ));
    }

    public function checkAction(Request $request, $resultId, $targetId, $source = 'course')
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!$result) {
            throw $this->createResourceNotFoundException('homeworkResult', $resultId);
        }

        $homework = $this->getTestpaperService()->getTestpaper($result['testId']);
        if (!$homework) {
            throw $this->createResourceNotFoundException('homework', $result['id']);
        }

        if ($result['status'] != 'reviewing') {
            return $this->redirect($this->generateUrl('homework_start_do', array('homeworkId' => $homework['id'], 'lessonId' => $result['lessonId'])));
        }

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->getTestpaperService()->checkFinish($result['id'], $formData);

            return $this->createJsonResponse(true);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($homework['id'], $result['id']);

        $essayQuestions = $this->getCheckedEssayQuestions($questions);

        $student = $this->getUserService()->getUser($result['userId']);

        return $this->render('homework/manage/teacher-check.html.twig', array(
            'paper'         => $homework,
            'paperResult'   => $result,
            'questions'     => $essayQuestions,
            'student'       => $student,
            'questionTypes' => array('essay', 'material'),
            'source'        => $source,
            'targetId'      => $targetId,
            'isTeacher'     => true,
            'total'         => array()
        ));
    }

    protected function getCheckedEssayQuestions($questions)
    {
        $essayQuestions = array();

        foreach ($questions as $question) {
            if ($question['type'] == 'essay' && !$question['parentId']) {
                $essayQuestions[$question['id']] = $question;
            } elseif ($question['type'] == 'material') {
                $types = ArrayToolkit::column($question['subs'], 'type');
                if (in_array('essay', $types)) {
                    $essayQuestions[$question['id']] = $question;
                }
            }
        }

        return $essayQuestions;
    }

    protected function getQuestionRanges($courseSetId)
    {
        $courses   = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $courseTasks = $this->getCourseTaskService()->findTasksByCourseIds($courseIds);
        return ArrayToolkit::index($courseTasks, 'id');
    }

    protected function sortType($types)
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
        return $this->createService('Course:CourseService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
