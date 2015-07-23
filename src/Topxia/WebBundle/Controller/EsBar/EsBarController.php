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
    public function studyPlanAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createAccessDeniedException('用户没有登录,不能查看!');
        }
        $memberConditions = array(
            'userId' => $user->id,
            'locked' => 0,
        );
        $sort = array('createdTime','DESC');
        $classrooms = array();
        $courses = array();
        $classroomIds = ArrayToolkit::column($this->getClassroomService()->searchMembers($memberConditions,$sort,0,5),'classroomId');
        if(!empty($classroomIds)){
            $classroomConditions = array(
                'classroomIds' => $classroomIds
            );
            $classrooms = $this->getClassroomService()->searchClassrooms($classroomConditions,$sort,0,5);
            foreach ($classrooms as $key => &$classroom){
                $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
                $courseIds = ArrayToolkit::column($courses,'id');
                /**
                 * 找出学过的课时
                 */
                $learnedConditions = array(
                    'userId' => $user->id,
                    'status' => 'finished',
                    'courseIds' => $courseIds
                );
                $learnedCount = $this->getCourseService()->searchLearnCount($learnedConditions);
                $sort = array( 'finishedTime','ASC');
                $learnedIds = ArrayToolkit::column($this->getCourseService()->searchLearns($learnedConditions,$sort,0,$learnedCount),'lessonId');
                /**
                 * 找出未学过的课时
                 */
                $notLearnedConditions = array(
                    'status' => 'published',
                    'courseIds' => $courseIds,
                    'notLearnedIds' => $learnedIds
                );
                $sort = array(
                    'seq','ASC'
                );
                $notLearnedLessons = $this->getCourseService()->searchLessons($notLearnedConditions,$sort,0,4);

                $classroomLessonNum = 0;
                foreach($courses as $course){   //迭代班级下课时总数
                    $classroomLessonNum += $course['lessonNum'];
                }

                if(empty($notLearnedLessons))
                {
                    unset($classrooms[$key]);
                }else{
                    foreach($notLearnedLessons as &$notLearnedLesson) {
                        $notLearnedLesson['isLearned'] = $this->getCourseService()->getUserLearnLessonStatus($user->id, $notLearnedLesson['courseId'], $notLearnedLesson['id']);
                    }
                    $classroom['lessons'] = $notLearnedLessons;
                    $classroom['learnedLessonNum'] = $learnedCount;
                    $classroom['allLessonNum'] = $classroomLessonNum;
                }

            }
        }
        $courseMemConditions = array(
            'userId' => $user->id,
            'locked' => 0,
            'classroomId' => 0
        );
        $courseIds =  ArrayToolkit::column($this->getCourseService()->searchMembers($courseMemConditions,array('createdTime','DESC'),0,5),'courseId');
        if(!empty($courseIds)){
            $courseConditions = array('courseIds' => $courseIds);
            $courses = $this->getCourseService()->searchCourses($courseConditions,'hitNum',0,5);

            foreach ($courses as $key => &$course){
                /**
                 * 找出学过的课时
                 */
                $learnedConditions = array(
                    'userId' => $user->id,
                    'status' => 'finished',
                    'courseId' => $course['id']
                );
                $sort = array( 'finishedTime','ASC');
                $learnedCount = $this->getCourseService()->searchLearnCount($learnedConditions);
                $learnedIds = ArrayToolkit::column($this->getCourseService()->searchLearns($learnedConditions,$sort,0,$learnedCount),'lessonId');
                /**
                 * 找出未学过的课时
                 */
                $notLearnedConditions = array(
                    'status' => 'published',
                    'courseId' => $course['id'],
                    'notLearnedIds' => $learnedIds
                );
                $sort = array(
                    'seq','ASC'
                );
                $notLearnedLessons = $this->getCourseService()->searchLessons($notLearnedConditions,$sort,0,4);

                if(empty($notLearnedLessons)){
                    unset($courses[$key]);
                }else{
                    foreach($notLearnedLessons as &$notLearnedLesson) {
                        $notLearnedLesson['isLearned'] = $this->getCourseService()->getUserLearnLessonStatus($user->id, $notLearnedLesson['courseId'], $notLearnedLesson['id']);
                    }
                    $course['lessons'] = $notLearnedLessons;
                    $course['learnedLessonNum'] = $learnedCount;
                    $course['allLessonNum'] = $course['lessonNum'];
                }
            }
        }

        return $this->render("TopxiaWebBundle:EsBar:ListContent/StudyCenter/study-mission.html.twig", array(
            'classrooms' => $classrooms,
            'courses' => $courses
        ));
    }

    public function courseAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createAccessDeniedException('用户没有登录,不能查看!');
        }
        $conditions = array(
            'userId' => $user->id
        );
        $sort = array('createdTime','DESC');
        $courseIds = ArrayToolkit::column($this->getCourseService()->searchMembers($conditions,$sort,0,100),'courseId');
        $courses = array();
        if(!empty($courseIds)){
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
        }

        return $this->render("TopxiaWebBundle:EsBar:ListContent/StudyPlace/my-course.html.twig", array(
            'courses' => $courses,
        ));
    }

    public function classroomAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createAccessDeniedException('用户没有登录,不能查看!');
        }
        $memberConditions = array(
            'userId' => $user->id,
            'locked' => 0,
        );
        $sort = array('createdTime','DESC');
        $classroomIds = ArrayToolkit::column($this->getClassroomService()->searchMembers($memberConditions,$sort,0,5),'classroomId');
        $classroomConditions = array(
            'classroomIds' => $classroomIds
        );
        $classrooms = array();
        if(!empty($classroomIds)){
            $classrooms = $this->getClassroomService()->searchClassrooms($classroomConditions,$sort,0,100);
        }
        return $this->render("TopxiaWebBundle:EsBar:ListContent/StudyPlace/my-classroom.html.twig", array(
            'classrooms' => $classrooms
        ));
    }

    /*public function historyAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $learnsConditions = array(
            'userId' => $user->id,
        );
        $sort = array( 'startTime','DESC');
        $lessonLearns = $this->getCourseService()->searchLearns($learnsConditions,$sort,0,1000);
        $homeworkResults = array();
        if($this->isPluginInstalled('Homework')){
            $conditions = array(
                'userId' => $user->id
            );
            $homeworkResults = $this->getHomeworkService()->searchResults(
                $conditions,
                array('usedTime', 'DESC'),
                0,
                1000
            );
        }
        $testPaperConditions = array(
            'userId' => $user->id
        );
        $testPaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $testPaperConditions,
            array('endTime', 'DESC'),
            0,
            1000
        );
        $histories = $this->getHistoryByTime($lessonLearns,$homeworkResults,$testPaperResults);
        return $this->render('TopxiaWebBundle:EsBar:history.html.twig', array(
            'histories' => $histories
        ));
    }*/

    public function notifyAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createAccessDeniedException('用户没有登录,不能查看!');
        }
        $notifications = $this->getNotificationService()->findUserNotifications(
            $user->id,
            0,
            100
        );
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);
        return $this->render('TopxiaWebBundle:EsBar:ListContent/notification/notify.html.twig', array(
            'notifications' => $notifications
        ));
    }

    public function practiceAction(Request $request,$status)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createAccessDeniedException('用户没有登录,不能查看!');
        }
        $homeworkResults = array();
        $testPaperResults = array();
        $courses = array();
        $lessons = array();
        if($this->isPluginInstalled('Homework')){
            $conditions = array(
                'status' => $status,
                'userId' => $user->id
            );
            $homeworkResults = $this->getHomeworkService()->searchResults(
                $conditions,
                array('createdTime', 'DESC'),
                0,
                10
            );
            $homeworkCourseIds = ArrayToolkit::column($homeworkResults, 'courseId');
            $homeworkLessonIds = ArrayToolkit::column($homeworkResults, 'lessonId');
            $courses = $this->getCourseService()->findCoursesByIds($homeworkCourseIds);
            $lessons = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);
        }



        $testPaperConditions = array(
            'status' => $status,
            'userId' => $user->id
        );

        $testPaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $testPaperConditions,
            array('usedTime', 'DESC'),
            0,
            10
        );

        return $this->render('TopxiaWebBundle:EsBar:ListContent/Practice/practice.html.twig', array(
            'testPaperResults' => $testPaperResults,
            'courses' => $courses,
            'lessons' => $lessons,
            'homeworkResults' => $homeworkResults,
            'status' => $status
        ));
    }

    /*private function getHistoryByTime($lessonLearns,$homeworks,$testPaperResults)
    {
        $history = array();
        if(!empty($lessonLearns)){
            foreach ($lessonLearns as &$lessonLearn){
                $lesson = $this->getCourseService()->getCourseLesson($lessonLearn['courseId'],$lessonLearn['lessonId']);
                $lessonLearn['lessonTitle'] = $lesson['title'];
                $data = $lessonLearn['status'] == 'finished' ? $lessonLearn['finishedTime'] : $lessonLearn['startTime'];
                $history[ date('Y/m/d',$lessonLearn['startTime']) ]['lesson'][] = $lessonLearn;
            }
        }
        if(!empty($homeworks)){
            foreach ($homeworks as &$homework){
                $lesson = $this->getCourseService()->getCourseLesson($lessonLearn['courseId'],$lessonLearn['lessonId']);
                $lessonLearn['lessonTitle'] = $lesson['title'];
                $history[ date('Y/m/d',$lessonLearn['startTime']) ]['lesson'][] = $lessonLearn;
            }
        }
        if(!empty($testPaperResults)){
            foreach ($testPaperResults as &$testPaperResult){
                $data = $testPaperResult['status'] == 'finished' ? $testPaperResult['checkedTime'] : $testPaperResult['endTime'];
                $history[ date('Y/m/d',$data) ]['testPaperResult'][] = $testPaperResult;
            }
        }
        return $history;
    }*/

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