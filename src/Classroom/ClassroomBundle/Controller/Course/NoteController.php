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
        $classroom_courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($classroom_courses, 'id');
        
        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render('ClassroomBundle:Classroom\Course:notes-list.html.twig', array(
            'layout' => $layout,
            'canLook' => $this->getClassroomService()->canLookClassroom($classroom['id']),
            'classroom' => $classroom,
            'courseIds' => $courseIds,
        ));
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
