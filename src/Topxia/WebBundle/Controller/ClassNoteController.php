<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ClassNoteController extends BaseController
{
    public function listAction(Request $request,$classId){
        $class = $this->getClassService()->getClass($classId);
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
        if(count($courses)>0){
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
        $userProfiles=$this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($notes, 'userId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($notes, 'lessonId'));
        $courses=ArrayToolkit::index($courses, 'id');
        return $this->render("TopxiaWebBundle:ClassNote:note-list.html.twig", array(
            'class' => $class,
            'classNav'=>'notes',
            'notes' => $notes,
            'paginator' => $paginator,
            'users'=>$users,
            'lessons'=>$lessons,
            'courses'=>$courses,
            'userProfiles'=>$userProfiles
        ));
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