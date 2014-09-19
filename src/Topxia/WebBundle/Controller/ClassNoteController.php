<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ClassNoteController extends ClassBaseController
{
    public function listAction(Request $request,$classId)
    {
        $class = $this->tryViewClass($classId);
        $user = $this->getCurrentUser();
        if (empty($class)) {
            throw $this->createNotFoundException("班级不存在，或已删除。");
        }
        $conditions = array(
            'classId'=>$class['id'],
            'gradeId'=>$class['gradeId'],
            'term'=>$class['term']
        );
        /**本班所有课程*/
        $courses=$this->getCourseService()->searchCourses($conditions,null, 0, PHP_INT_MAX);
        $notes=array();
        $paginator = new Paginator(
            $this->get('request'),
            0,
            20
        ); 
        if(count($courses)>0) {
            $conditions = array(
                'status'=>1,
                'courseIds'=>ArrayToolkit::column($courses, 'id')
            );
            $paginator = new Paginator(
                $this->get('request'),
                $this->getNoteService()->searchNoteCount($conditions),
                20
            );

            $notes=$this->getNoteService()->searchNotes(
                $conditions,
                'created',
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($notes, 'userId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($notes, 'lessonId'));
        $courses=ArrayToolkit::index($courses, 'id');

        $userPraises=$this->getNoteService()->findNotePraisesByUserId($user['id']);
        $userPraises=ArrayToolkit::index($userPraises, 'noteId');
        $notePraises=$this->getNoteService()->findNotePraisesByNoteIds(ArrayToolkit::column($notes, 'id'));    
        return $this->render("TopxiaWebBundle:ClassNote:note-list.html.twig", array(
            'class' => $class,
            'classNav'=>'notes',
            'notes' => $notes,
            'paginator' => $paginator,
            'users'=>$users,
            'lessons'=>$lessons,
            'courses'=>$courses,
            'userPraises'=>$userPraises,
            'notePraises'=>$notePraises
        ));
    }

    public function praiseAction(Request $request,$noteId)
    {
        $note=$this->getNoteService()->getNote($noteId);
        $user=$this->getCurrentUser();
        if(empty($note)){
            throw $this->createNotFoundException("笔记不存在，或已删除。");
        }
        $praise=$this->getNoteService()->getNotePraiseByNoteIdAndUserId($noteId,$user['id']);
        if(!empty($praise)){
            throw $this->createAccessDeniedException('不可重复对一条笔记点赞！');
        }
        $this->getNoteService()->praise($noteId);
        return $this->createJsonResponse($this->getNoteService()->findNotePraisesByNoteId($noteId));
    }

    public function canclePraiseAction(Request $request,$noteId)
    {
        $note=$this->getNoteService()->getNote($noteId);
        if(empty($note)){
            throw $this->createNotFoundException("笔记不存在，或已删除。");
        }
        $this->getNoteService()->canclePraise($noteId);
        return $this->createJsonResponse($this->getNoteService()->findNotePraisesByNoteId($noteId));
    }


    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}