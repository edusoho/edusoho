<?php
namespace Topxia\WebBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\CourseBaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class NoteController extends CourseBaseController
{
    public function listAction(Request $request, $courseIds, $filters)
    {
        $conditions = $this->convertFiltersToConditions($courseIds, $filters);

        $notes = array();
        $result['notes'] = $notes;
        
        if ((isset($conditions['courseIds']) && !empty($conditions['courseIds'])) || 
            (isset($conditions['courseId']) && !empty($conditions['courseId']))) {

            $paginator = new Paginator(
                $request,
                $this->getNoteService()->searchNoteCount($conditions),
                20
            );
            $orderBy = $this->convertFiltersToOrderBy($filters);

            $notes = $this->getNoteService()->searchNotes(
                $conditions,
                $orderBy,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            $result = $this->makeNotesRelated($notes, $courseIds);
            $result['paginator'] = $paginator;
        }


        return $this->render('TopxiaWebBundle:Course\Note:notes-list.html.twig', $result);
    }

    public function showListAction(Request $request, $courseId)
    {
        list($course, $member) = $this->buildCourseLayoutData($request, $courseId);
        if($course['parentId']){
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);
            
            $classroomSetting = $this->setting('classroom',array());
            $classroomName    = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';
        
            if(!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])){ 
                return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}课程，如有需要请联系客服",'',3,$this->generateUrl('homepage'));
            }
        }
        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        return $this->render('TopxiaWebBundle:Course\Note:course-notes-list.html.twig', array(
            'course' => $course,
            'member' => $member,
            'filters' => $this->getNoteSearchFilters($request),
            'lessons' => $lessons
        ));
    }

    protected function getNoteSearchFilters($request)
    {
        $filters = array();
        
        $filters['lessonId'] = $request->query->get('lessonId', '');
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('latest', 'likeNum'))) {
            $filters['sort'] = 'latest';
        }

        return $filters;
    }


    public function likeAction(Request $request, $noteId)
    {
        $this->getNoteService()->like($noteId);
        $note = $this->getNoteService()->getNote($noteId);
        
        return $this->createJsonResponse($note);
    }

    public function cancelLikeAction(Request $request, $noteId)
    {

        $note =  $this->getNoteService()->cancelLike($noteId);
        $note = $this->getNoteService()->getNote($noteId);
        
        return $this->createJsonResponse($note);
    }

    protected function makeNotesRelated($notes, $courseIds)
    {
        $user = $this->getCurrentUser();
        $result = array();
        $noteLikes = $this->getNoteService()->findNoteLikesByNoteIdsAndUserId(ArrayToolkit::column($notes, 'id'), $user['id']);
        $userIds = ArrayToolkit::column($notes, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $result['noteLikes'] = $noteLikes;
        $result['users'] = $users;
        $lessonIds = ArrayToolkit::column($notes, 'lessonId');
        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);
        $result['lessons'] = $lessons;
        if (is_array($courseIds)) {
            $courseIds = ArrayToolkit::column($notes, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $result['courses'] = $courses;
        }

        $result['notes'] = $notes;
        return $result;
    }

    protected function convertFiltersToConditions($courseIds, $filters)
    {
        $conditions = array(
            'status' => 1,
        );

        if (is_numeric($courseIds)) {
            $conditions['courseId'] = $courseIds;
        }

        if (!empty($filters['courseId'])) {
            $conditions['courseId'] = $filters['courseId'];
        }

        if (is_array($courseIds) && empty($filters['courseId'])) {
            $conditions['courseIds'] = $courseIds;
        }

        if (!empty($filters['lessonId'])) {
            $conditions['lessonId'] = $filters['lessonId'];
        }


        return $conditions;
    }

    protected function convertFiltersToOrderBy($filters)
    {
        $orderBy = array();
        switch ($filters['sort']) {
            case 'latest':
                $orderBy['updatedTime'] = 'DESC';
                break;
            case 'likeNum':
                $orderBy['likeNum'] = 'DESC';
                break;
            default:
                $orderBy['updatedTime'] = 'DESC';
                break;
        }
        return $orderBy;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }
}
