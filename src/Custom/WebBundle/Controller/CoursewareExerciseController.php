<?php 

namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\BaseController;

class CoursewareExerciseController extends BaseController
{
    public function showAction(Request $request,$id, $lessonId, $courseId)
    {
    	$courseware = $this->getCoursewareService()->getCourseware($id);
    	return $this->render('CustomWebBundle:CoursewareExercise:show.html.twig',array(
    		'courseware' => $courseware,
    		'lessonId' => $lessonId,
    		'courseId' => $courseId,
    	));
    }

    public function addAction(Request $request, $lessonId, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        if ($request->getMethod() == 'POST') {
            $questionId = $request->query->get('questionId');
            $showtime = $request->query->get('showtime');
            $showtime = explode(':', $showtime);
            $showtime = (int)$showtime[0]*60+(int)$showtime[1];
            $this->getExerciseService()->createMediaLessonExercise(array('lessonId' => $lessonId, 'questionId' => $questionId, 'showtime' => $showtime));
            list($mediaExercises, $questions) = $this->buildMediaExercises($lessonId);
            return $this->render('HomeworkBundle:CourseExerciseManage:execise-questions-table.html.twig', array(
                'exercises' => $mediaExercises,
                'questions' => $questions,
                'course' => $course,
            ));
        }
        list($questions, $paginator, $tags, $choosedTags, $showtime) = $this->buildQuestions($request);
    	$lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $courseware = $this->getCoursewareService()->getCourseware($lesson['mediaId']);
    	$mainKnowledge = $this->getKnowledgeService()->getKnowledge($courseware['mainKnowledgeId']);
        
    	return $this->render('CustomWebBundle:CoursewareExercise:add-modal.html.twig',array(
    		'lesson' => $lesson,
            'questions' => $questions,
            'paginator' => $paginator,
            'tags' => $tags,
            'choosedTags' => $choosedTags,
    		'mainKnowledge' => $mainKnowledge,
            'showtime' => $showtime,
            'position' => $request->query->get('position'),
            'lessonId' => $lessonId,
            'courseId' => $courseId,
    	));
    }

    private function buildMediaExercises($lessonId)
    {
        $mediaExercises = $this->getExerciseService()->findMediaLessonExercisesByLessonId($lessonId);
        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($mediaExercises, 'questionId'));
        foreach ($mediaExercises as $key => $exercise) {
            $exercise['showtime'] = date('H:i', mktime(0,(int)$exercise['showtime']));
            $mediaExercises[$key] = $exercise;
        }
        return array($mediaExercises, $questions);
    }

    private function buildQuestions($request)
    {
        $conditions = $request->query->all();
        $conditions['types'] = array('choice', 'single_choice', 'uncertain_choice', 'determine');
        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionService()->searchQuestionsCount($conditions)
            , 10
        );
        $questions = $this->getQuestionService()->searchQuestions(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $tagIds = ArrayToolkit::all($questions, 'tagIds');
        $tags = $this->getTagService()->findTagsByIds($tagIds);
        $tags = ArrayToolkit::index($tags, 'id');
        $choosedTags = array();
        if(!empty($conditions['tagIds'])) {
            $choosedTagIds = explode(',', $conditions['tagIds']);
            $choosedTags = $this->getTagService()->findTagsByIds($choosedTagIds);    
        }
        $showtime = date('H:i', mktime(0,(int)$conditions['position']));
        return array($questions, $paginator, $tags, $choosedTags, $showtime);
    }

    private function getCoursewareService()
    {
        return $this->getServiceKernel()->createService('Courseware.CoursewareService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Tag.TagService');
    }

    private function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    }
}