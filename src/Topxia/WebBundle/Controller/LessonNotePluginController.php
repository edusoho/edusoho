<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class LessonNotePluginController extends BaseController
{

    public function initAction (Request $request)
    {
        $currentUser = $this->getCurrentUser();
        
        $course = $this->getCourseService()->getCourse($request->query->get('courseId'));
        $lesson = array('id' => $request->query->get('lessonId'),'courseId' => $course['id']);
        $note = $this->getCourseNoteService()->getUserLessonNote($currentUser['id'], $lesson['id']);
        $formInfo = array(
            'courseId' => $course['id'], 
            'lessonId' => $lesson['id'],
            'content'=>$note['content'],
            'id'=>$note['id'],
        );
        $form = $this->createNoteForm($formInfo);
        return $this->render('TopxiaWebBundle:LessonNotePlugin:index.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function saveAction(Request $request)
    {
        $form = $this->createNoteForm();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $note = $form->getData();
                $this->getCourseNoteService()->saveNote($note);
                return $this->createJsonResponse(true);
            } else {
                return $this->createJsonResponse(false);
            }
        }
        return $this->createJsonResponse(false);
    }

    private function createNoteForm($data = array())
    {
        return $this->createNamedFormBuilder('note', $data)
            ->add('id', 'hidden', array('required' => false))
            ->add('content', 'textarea',array('required' => false))
            ->add('courseId', 'hidden', array('required' => false))
            ->add('lessonId', 'hidden', array('required' => false))
            ->getForm();
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCourseNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }
}