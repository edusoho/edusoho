<?php 

namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\BaseController;

class CCViedoManageController extends BaseController
{
    public function showAction(Request $request, $courseId)
    {
        $lessonId = $request->query->get('lessonId');
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $courseware = $this->getCoursewareService()->getCourseware($lesson['coursewareId']);
        return $this->render('CustomWebBundle:CCViedoManage:CCViedoTemplate.html.twig',array(
            'courseware' => $courseware
        ));
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