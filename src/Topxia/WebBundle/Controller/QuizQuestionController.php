<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class QuizQuestionController extends BaseController
{
	public function indexAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$LessonIds = ArrayToolkit::column($this->getCourseService()->getCourseLessons($courseId),'id');

		$conditions['target']['course'] = $courseId;
		if(isset($LessonIds)){
			$conditions['target']['lesson'] = $LessonIds;
		}

		$paginator = new Paginator(
			$this->get('request'),
			$this->getQuestionService()->searchQuestionCount($conditions),
			10
		);

		$questions = $this->getQuestionService()->searchQuestion(
			$conditions,
			array('createdTime' ,'DESC'),
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount()
		);

		$lessons = array();
		foreach ($questions as $question) {
			if ($question['targetType'] == 'lesson'){
				$lessons[] = $question;
			}
		}

		$lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($lessons,'targetId'));

		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId')); 

		return $this->render('TopxiaWebBundle:CourseManage:question.html.twig', array(
			'course' => $course,
			'questions' => $questions,
			'users' => $users,
			'lessons' => $lessons,
			'paginator' => $paginator,
		));
	}

	public function createAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$type = $request->query->get('type');
		if ($request->getMethod() == 'POST') {
            $content = $request->request->all();
            ArrayToolkit::dx($content);
            //$content['type'] = $type->getAlias();
            $file = $request->files->get('picture');
            if(!empty($file)){
                $record = $this->getFileService()->uploadFile('default', $file);
                $content['picture'] = $record['uri'];
            }

            $content = $this->getContentService()->createContent($this->convertContent($content));
            return $this->render('TopxiaAdminBundle:Content:content-tr.html.twig',array(
                'content' => $content,
                'category' => $this->getCategoryService()->getCategory($content['categoryId']),
                'user' => $this->getCurrentUser(),
            ));
        }

		if (!in_array($type, array('choice', 'fill', 'material', 'essay', 'determine'))) {
			$type = 'choice';
		}
		
		$targets = $this->getQuestionService()->getQuestionTarget($courseId);

		return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'course' => $course,
			'type' => $type,
			'targets' => $targets,
		));
	}


	private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

   	private function getQuestionService()
   	{
   		return $this -> getServiceKernel() -> createService('Quiz.QuestionService');
   	}

}
