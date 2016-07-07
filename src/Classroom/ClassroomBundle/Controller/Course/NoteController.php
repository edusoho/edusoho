<?php
namespace Classroom\ClassroomBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;

class NoteController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $classroomCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($classroomCourses, 'id');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $user = $this->getCurrentUser();

        $classroomSetting = $this->setting('classroom',array());
        $classroomName    = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';
        
        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        if(!$this->getClassroomService()->canLookClassroom($classroom['id'])){ 
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服",'',3,$this->generateUrl('homepage'));
        }

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }
        if(!$classroom){
            $classroomDescription = array();
        }
        else{
        $classroomDescription = $classroom['about'];
        $classroomDescription = strip_tags($classroomDescription,'');
        $classroomDescription = preg_replace("/ /","",$classroomDescription);
    }
        return $this->render('ClassroomBundle:Classroom\Course:notes-list.html.twig', array(
            'layout' => $layout,
            'filters' => $this->getNoteSearchFilters($request),
            'canLook' => $this->getClassroomService()->canLookClassroom($classroom['id']),
            'classroom' => $classroom,
            'courseIds' => $courseIds,
            'courses' => $courses,
            'member' => $member,
            'classroomDescription' => $classroomDescription
        ));
    }

    private function getNoteSearchFilters($request)
    {
        $filters = array();

        $filters['courseId'] = $request->query->get('courseId', '');
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('latest', 'likeNum'))) {
            $filters['sort'] = 'latest';
        }

        return $filters;
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
