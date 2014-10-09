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
        //用户点赞的笔记(NoteLike对象)
        $userLikes=$this->getNoteService()->findNoteLikesByUserId($user['id']);
        $userLikes=ArrayToolkit::index($userLikes, 'noteId');
        //每个笔记点赞的人列表(NoteLike对象)
        $noteLikes=$this->getNoteService()->findNoteLikesByNoteIds(ArrayToolkit::column($notes, 'id'));    
        $noteLikes=ArrayToolkit::group($noteLikes,'noteId');
        return $this->render("TopxiaWebBundle:ClassNote:note-list.html.twig", array(
            'class' => $class,
            'classNav'=>'notes',
            'notes' => $notes,
            'paginator' => $paginator,
            'users'=>$users,
            'lessons'=>$lessons,
            'courses'=>$courses,
            'userLikes'=>$userLikes,
            'noteLikes'=>$noteLikes
        ));
    }

    public function likeAction(Request $request,$noteId)
    {
        $note=$this->getNoteService()->getNote($noteId);
        $user=$this->getCurrentUser();
        if(empty($note)){
            throw $this->createNotFoundException("笔记不存在，或已删除。");
        }
        $like=$this->getNoteService()->getNoteLikeByNoteIdAndUserId($noteId,$user['id']);
        if(!empty($like)){
            throw $this->createAccessDeniedException('不可重复对一条笔记点赞！');
        }
        $this->getNoteService()->like($noteId);
        return $this->createJsonResponse($this->getLikeUsersText($noteId));
    }

    public function cancleLikeAction(Request $request,$noteId)
    {
        $note=$this->getNoteService()->getNote($noteId);
        if(empty($note)){
            throw $this->createNotFoundException("笔记不存在，或已删除。");
        }
        $this->getNoteService()->cancleLike($noteId);
        return $this->createJsonResponse($this->getLikeUsersText($noteId));
    }

    private function getLikeUsersText($noteId){
        $users=$this->getNoteService()->findNoteLikesByNoteId($noteId);
        $html="";

        if(count($users)>4){
            for ($i = 0 ; $i < 4 ; $i++) {
                $showUrl = $this->generateUrl('user_show', array('id'=> $users[$i]['userId']), true);
                $html.="、<a href='{$showUrl}'>".$users[$i]['truename']."</a>";
            }
            $html=mb_substr($html,1,strlen($html),'utf-8').'等'.count($users).'人赞过';
        }else if(!empty($users)){
            foreach ($users as $user) {
                $showUrl = $this->generateUrl('user_show', array('id'=> $user['userId']), true);
                $html.="、<a href='{$showUrl}'>".$user['truename']."</a>";
            }
            $html=mb_substr($html,1,strlen($html),'utf-8').'赞过';
        }else{
            return $html;
        }
        return $html;
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