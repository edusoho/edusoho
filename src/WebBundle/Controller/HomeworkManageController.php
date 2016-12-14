<?php

namespace WebBundle\Controller;

use Topxia\Common\Paginator;
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
            'targetChoices' => $this->getQuestionRanges($courseSet),
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
        if ($question['subCount'] > 0) {
            $subQuestions = $this->getQuestionService()->findQuestionsByParentId($question['id']);
        }

        return $this->render('homework/manage/question-picked-tr.html.twig', array(
            'courseSet'    => $courseSet,
            'question'     => $question,
            'subQuestions' => $subQuestions,
            'type'         => $question['type'],
            'target'       => $request->query->get('target', 'testpaper')
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

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
