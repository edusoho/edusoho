<?php
/**
 * author: retamia
 * Time: 15-7-7 上午11:12
 * description: 
 */
namespace Topxia\WebBundle\Controller\EsBar;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class EsBarController extends BaseController{
    public function liveNotifyAction(Request $request,$userId)
    {
        $filters['type'] = 'live';
        $liveCourses = $this->getCourseService()->findUserLeaningCourses($userId,0,100,$filters);
        $liveLessons = array();
        if(!empty($liveCourses)){
            $conditions = array(
                'status' => 'published',
                'courseIds' => ArrayToolkit::column($liveCourses,'id'),
                'type' => 'live',
                'startTimeGreaterThan' => time()
            );
            $sort = array(
                'startTime','ASC'
            );
            $liveLessons = $this->getCourseService()->searchLessons($conditions,$sort,0,2);
        }
        return $this->render("TopxiaWebBundle:EsBar:live-notify.html.twig", array(
            'liveLessons' => $liveLessons,
        ));
    }

    public function studyPlanAction(Request $request,$userId)
    {
        $memberConditions = array(
            'userId' => $userId,
            'locked' => 0,
        );
        $sort = array('createdTime','DESC');
        $classrooms = array();
        $classroomIds = ArrayToolkit::column($this->getClassroomService()->searchMembers($memberConditions,$sort,0,5),'classroomId');
        $classroomLessons = array();
        if(!empty($classroomIds)){
            $classroomConditions = array(
                'classroomIds' => $classroomIds
            );
            $classrooms = $this->getClassroomService()->searchClassrooms($classroomConditions,$sort,0,5);
            foreach ($classrooms as $key => &$classroom){
                $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
                $courseIds = ArrayToolkit::column($courses,'id');
                $user = $this->getCurrentUser();
                $learnedConditions = array(
                    'userId' => $user->id,
                    'status' => 'finished',
                    'courseIds' => $courseIds
                );
                $sort = array( 'finishedTime','ASC');
                $learnedIds = ArrayToolkit::column($this->getCourseService()->searchLearns($learnedConditions,$sort,0,1000),'lessonId');

                $notLearnedConditions = array(
                    'status' => 'published',
                    'courseIds' => $courseIds,
                    'notLearnedIds' => $learnedIds
                );
                $sort = array(
                    'createdTime','ASC'
                );
                $notLearnedLessons = $this->getCourseService()->searchLessons($notLearnedConditions,$sort,0,5);
                $allLessonConditions = array(
                    'status' => 'published',
                    'courseIds' => $courseIds,
                    'notLearnedIds' => $learnedIds
                );
                $sort = array(
                    'createdTime','ASC'
                );
                $allLessons = $this->getCourseService()->searchLessons($allLessonConditions,$sort,0,1000    );
                if(empty($notLearnedLessons))
                {
                    unset($classrooms[$key]);
                }else{
                    $classroomLessons[$classroom['id']] = $notLearnedLessons;
                    $classroom['learnedLessonNum'] = count($learnedIds);
                    $classroom['allLessonNum'] = count($allLessons);
                }

            }
        }
        return $this->render("TopxiaWebBundle:EsBar:study-plan.html.twig", array(
            'classrooms' => $classrooms,
            'classroomLessons' => $classroomLessons
        ));
    }

    public function myCourseOrClassroomAction(Request $request,$type)
    {
        $user = $this->getCurrentUser();
        switch($type){
            case 'classroom':
                $memberConditions = array(
                    'userId' => $user->id,
                    'locked' => 0,
                );
                $sort = array('createdTime','DESC');
                $classroomIds = ArrayToolkit::column($this->getClassroomService()->searchMembers($memberConditions,$sort,0,5),'classroomId');
                $classroomConditions = array(
                    'classroomIds' => $classroomIds
                );
                $classrooms = $this->getClassroomService()->searchClassrooms($classroomConditions,$sort,0,100);

                return $this->render("TopxiaWebBundle:EsBar:my-course-classroom.html.twig", array(
                    'classrooms' => $classrooms,
                    'type' => $type
                ));
                break;
            case 'course':
                $conditions = array(
                    'userId' => $user->id
                );
                $sort = array('createdTime','DESC');
                $courseIds = ArrayToolkit::column($this->getCourseService()->searchMembers($conditions,$sort,0,100),'courseId');
                $courseConditions = array(
                    'courseIds' => $courseIds
                );
                $courses = $this->getCourseService()->searchCourses($courseConditions,'hitNum',0,100);
                foreach($courses as &$course) {
                    $member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);
                    if( $course['lessonNum'] != 0) {
                        $course['percent'] = intval($member['learnedNum'] / $course['lessonNum'] * 100);
                    }else{
                        $course['percent'] = 0;
                    }
                }
                return $this->render("TopxiaWebBundle:EsBar:my-course-classroom.html.twig", array(
                    'courses' => $courses,
                    'type' => $type
                ));
                break;
            default:
                throw $this->createNotFoundException('类型不确定,类型为班级或课程');
                break;
        }
    }

    public function studyHistoryAction(Request $request)
    {

    }

    public function notifyAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $notifications = $this->getNotificationService()->findUserNotifications(
            $user->id,
            0,
            100
        );
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);
        return $this->render('TopxiaWebBundle:EsBar:notify.html.twig', array(
            'notifications' => $notifications
        ));
    }

    public function practiceAction(Request $request,$status)
    {
        $user = $this->getCurrentUser();
        $homeworks = array();
        $testPaperResults = array();
        if($this->isPluginInstalled('Homework')){
            $conditions = array(
                'status' => $status,
                'userId' => $user->id
            );
            $homeworkResults = $this->getHomeworkService()->searchResults(
                $conditions,
                array('usedTime', 'DESC'),
                1,
                100
            );
            $homeworkLessonIds = ArrayToolkit::column($homeworkResults, 'lessonId');
            $homeworks = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);
        }

        $testPaperConditions = array(
            'status' => $status,
            'userId' => $user->id
        );

        $testPaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $testPaperConditions,
            array('usedTime', 'DESC'),
            1,
            100
        );
        return $this->render('TopxiaWebBundle:EsBar:practice.html.twig', array(
            'testPaperResults' => $testPaperResults,
            'homeworks' => $homeworks
        ));
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

}