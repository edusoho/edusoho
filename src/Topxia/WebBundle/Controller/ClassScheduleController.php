<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassScheduleController extends ClassBaseController
{
    public function showAction(Request $request, $classId)
    {
        $class = $this->tryViewSchedule($classId);
        return $this->render("TopxiaWebBundle:ClassSchedule:show.html.twig", array(
            "class" => $class,
        ));
    }

    public function coursesAction(Request $request, $classId)
    {
        $class = $this->tryViewSchedule($classId);
        $user = $this->getCurrentUser();
        $courses = array();
        $conditions =array(
            'classId' => $class['id'],
            'status' => 'published',
            'gradeId' => $class['gradeId'],
            'term' => $class['term']
        );

        $total = $this->getCourseService()->searchCourseCount($conditions);

        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, $total);

        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        
        $teachCourses = array();
        if($user->isTeacher()) {
           foreach ($courses as $course) {
               if(in_array($user['id'], $course['teacherIds'])) {
                   $teachCourses[] = $course;
               }
           }

        } 
        
        return $this->render('TopxiaWebBundle:ClassSchedule:courses.html.twig', array(
            'courses' => $courses,
            'teachCourses' => $teachCourses,
            'users' => $users,
            'classId' => $class['id'],
            ));
    }

    public function getItemsAction($courseId)
    {
        $items = $this->getCourseService()->getCourseItems($courseId);

        foreach ($items as $key => $item) {
            if($item['itemType'] == 'lesson' && $item['status'] != 'published') {
                unset($items[$key]);
            }
        }
        
        $course = $this->getCourseService()->getCourse($courseId);
        return $this->render('TopxiaWebBundle:ClassSchedule:item-list.html.twig', array(
            'items' => $items,
            'course' => $course,
            ));
    }

    public function scheduleAction(Request $request, $classId)
    {
        $this->tryViewSchedule($classId);
        $user = $this->getCurrentUser();
        $previewAs = $request->query->get('previewAs') ? : 'week';
        $sunDay = $request->query->get('sunday');
        $period = $request->query->get('date');
        $year = $request->query->get('year');
        $month = $request->query->get('month');
        $mode = $request->query->get('mode');
        
        if($previewAs == 'week') {
            $results = $this->getScheduleService()->findScheduleLessonsByWeek($classId, $sunDay, $mode);
        } else {
            $period = $this->getPeriod($year,$month);
            $results = $this->getScheduleService()->findScheduleLessonsByMonth($classId, $period);            
            
            return $this->render("TopxiaWebBundle:ClassSchedule:{$previewAs}-view.html.twig", array(
            'courses' => $results['courses'],
            'lessons' => $results['lessons'],
            'schedules' => $results['schedules'],
            'teachers' => $results['teachers'],
            'year' => $year,
            'month' => $month,
            'period' => $period,
            ));
        }

        $userLessonLearns = $this->getCourseService()->findUserLessonLearns($user['id']);
        $userLessonLearns =ArrayToolkit::index($userLessonLearns, 'lessonId');
        return $this->render("TopxiaWebBundle:ClassSchedule:{$previewAs}-view.html.twig", array(
            'courses' => $results['courses'],
            'lessons' => $results['lessons'],
            'changeMonth' => $results['changeMonth'],
            'schedules' => $results['schedules'],
            'lessonLearns' => $userLessonLearns,
            'mode' => $mode,
            'mySchedules' => $results['mySchedules'],
            ));
    }

    public function saveAction(Request $request, $classId)
    {
        $this->tryManageSchedule($classId);
        $lessons = $request->request->all();
        $lessonIds = $lessons['ids'] == '' ? array() : explode(',', $lessons['ids']);
        $schedules = array();
        foreach ($lessonIds as $index => $id) {
            $schedule['classId'] = $classId;
            $schedule['lessonId'] = $id;
            $schedule['sequence'] = $index + 1;
            $schedule['date'] = $lessons['day'];
            $schedule['createdTime'] = time();
            $schedules[] = $schedule;    
        }
        $this->getScheduleService()->saveSchedules($classId, $schedules, $lessons['day']);
        return new Response("success");
    }

    private function getPeriod($year, $month)
    {
        $period = array();
        $month = intval($month);
        $nextMonth = $month + 1;
        $previousMonth = $month - 1; 
        $daysInMonth = date('t', strtotime($year.'/'.$month.'/'.'01'));
        $weekFirstDay = date('w', strtotime($year.'/'.$month.'/'.'01'));
        $weekLastDay = date('w', strtotime($year.'/'.$month.'/'.$daysInMonth));
        $previsousMonthDays = date('t', strtotime($year.'/'.$previousMonth.'/'.'01'));
        $nextMonthDays = date('t', strtotime($year.'/'.$nextMonth.'/'.'01'));
        for ($i=0; $i < $weekFirstDay ; $i++) { 
            $period[] = '' . $year . ($previousMonth>9?$previousMonth:'0'.$previousMonth) . ($previsousMonthDays-$weekFirstDay+$i+1);
        }

        for ($i=1; $i < $daysInMonth +1 ; $i++) { 
            $period[] = '' . $year . ($month>9?$month:'0'.$month) . (($i>9)?$i:('0'.$i));
        } 

        for ($i=0; $i < 6-$weekLastDay; $i++) { 
            $period[] = '' . $year . ($nextMonth>9?$nextMonth:'0'.$nextMonth) . ('0'.($i+1));
        }

        return $period;
    }

    private function getScheduleService()
    {
        return $this->getServiceKernel()->createService('Schedule.ScheduleService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}