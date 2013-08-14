<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseNoteController extends BaseController
{
	public function indexAction(Request $request)
	{
    	$form = $this->createFormBuilder()
    		->add('keywords', 'text', array('required' => false))
    		->add('nickname', 'text', array('required' => false))
			->getForm();
		$form->bind($request);
		$conditions = $form->getData();

		$convertConditions = $this->convertConditions($conditions);
        $paginator = new Paginator(
            $request,
            $this->getNoteService()->searchNoteCount($convertConditions),
            20
        );
        $notes = $this->getNoteService()->searchNotes(
            $convertConditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($notes, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($notes, 'courseId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($notes, 'lessonId'));
		return $this->render('TopxiaAdminBundle:CourseNote:index.html.twig',array(
    		'form' => $form->createView(),
    		'paginator' => $paginator,
            'notes' => $notes,
            'users'=>$users,
            'lessons'=>$lessons,
            'courses'=>$courses
		));
	}

	private function convertConditions($conditions)
	{
		if (!empty($conditions['nickname'])) {
			$user = $this->getUserService()->getUserByNickname($conditions['nickname']);
			if (empty($user)) {
				throw $this->createNotFoundException(sprintf("昵称为%s的用户不存在", $conditions['nickname']));
			}

			$conditions['userId'] = $user['id'];
		}
		unset($conditions['nickname']);

		if (empty($conditions['keywords'])) {
			unset($conditions['keywords']);
		}

		return $conditions;
	}

    public function deleteChoosedNotesAction(Request $request)
    {
        $ids = $request->request->get('ids');
        $result = $this->getNoteService()->deleteNotes($ids);
        
        if($result){
           return $this->createJsonResponse(array("status" =>"success")); 
       } else {
           return $this->createJsonResponse(array("status" =>"failed")); 
       }
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getCourseService()
    {
    	return $this->getServiceKernel()->createService('Course.CourseService');
    }
}