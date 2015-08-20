<?php
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

        $classrooms = $this->getClassroomStudyMissions();

        $courses = $this->getCourseStudyMissions();

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
            'userId' => $user->id,
            'locked' => 0,
            'classroomId' => 0,
            'role' => 'student'
        );
        $sort = array('createdTime','DESC');
        $members = $this->getCourseService()->searchMembers($conditions,$sort,0,15);
        $courseIds =  ArrayToolkit::column($members,'courseId');
        $courseConditions = array(
            'courseIds' => $courseIds,
            'parentId' => 0
        );
        $courses = $this->getCourseService()->searchCourses($courseConditions, 'default', 0, 15);
        $courses = ArrayToolkit::index($courses, 'id');
        $sortedCourses = array();
        if(!empty($courses)){
            foreach ($members as $member) {
                if (empty($courses[$member['courseId']])) {
                    continue;
                }
                $course = $courses[$member['courseId']];

                if( $course['lessonNum'] != 0) {
                    $course['percent'] = intval($member['learnedNum'] / $course['lessonNum'] * 100);
                }else{
                    $course['percent'] = 0;
                }

                $sortedCourses[] = $course;
            }
        }

        return $this->render("TopxiaWebBundle:EsBar:ListContent/StudyPlace/my-course.html.twig", array(
            'courses' => $sortedCourses,
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
            'role' => 'student'
        );
        $sort = array('createdTime','DESC');

        $members = $this->getClassroomService()->searchMembers($memberConditions,$sort,0,15);

        $classroomIds = ArrayToolkit::column($members,'classroomId');
        $classrooms = array();
        $sortedClassrooms = array();
        if(!empty($classroomIds)){
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
        }

        foreach ($members as $member) {
            if (empty($classrooms[$member['classroomId']])) {
                continue;
            }
            $classroom = $classrooms[$member['classroomId']];

            $sortedClassrooms[] = $classroom;
        }

        return $this->render("TopxiaWebBundle:EsBar:ListContent/StudyPlace/my-classroom.html.twig", array(
            'classrooms' => $sortedClassrooms
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
            15
        );
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);
        return $this->render('TopxiaWebBundle:EsBar:ListContent/Notification/notify.html.twig', array(
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
                array('updatedTime', 'DESC'),
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
            array('endTime', 'DESC'),
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

    private function getCourseStudyMissions()
    {
        $user = $this->getCurrentUser();

        $sortedCourses = array();

        $courseMemConditions = array(
            'userId' => $user->id,
            'locked' => 0,
            'classroomId' => 0,
            'role' => 'student'
        );

        $courseMem = $this->getCourseService()->searchMembers($courseMemConditions,array('createdTime','DESC'),0,5);
        $courseIds =  ArrayToolkit::column($courseMem,'courseId');
        if(!empty($courseIds)){
            $courseConditions = array(
                'courseIds' => $courseIds,
                'parentId' => 0
            );
            $courses = $this->getCourseService()->searchCourses($courseConditions, 'default', 0, 5);
            $courses = ArrayToolkit::index($courses, 'id');

            foreach ($courseMem as $member) {
                if (empty($courses[$member['courseId']])) {
                    continue;
                }
                $course = $courses[$member['courseId']];
                $sortedCourses[] = $course;
            }
            foreach ($sortedCourses as $key => &$course){
                /**
                 * 找出学过的课时
                 */

                $learnedConditions = array(
                    'userId' => $user->id,
                    'status' => 'finished',
                    'courseId' => $course['id']
                );
                $sort = array( 'finishedTime','ASC');
                $learneds = $this->getCourseService()->findUserLearnedLessons($user->id, $course['id']);
                /**
                 * 找出未学过的课时
                 */
                $learnedsGroupStatus = ArrayToolkit::group($learneds, 'status');

                $finishs = isset($learnedsGroupStatus['finished']) ? $learnedsGroupStatus['finished'] : array();
                $finishIds = ArrayToolkit::column($finishs, 'lessonId');

                $learnings = isset($learnedsGroupStatus['learning']) ? $learnedsGroupStatus['learning'] : array();
                $learningsIds = ArrayToolkit::column($learnings, 'lessonId');

                $notLearnedConditions = array(
                    'status' => 'published',
                    'courseId' => $course['id'],
                    'notLearnedIds' => $finishIds
                );

                $sort = array(
                    'seq','ASC'
                );
                $notLearnedLessons = $this->getCourseService()->searchLessons($notLearnedConditions,$sort,0,4);

                if(empty($notLearnedLessons)){
                    unset($sortedCourses[$key]);
                }else{
                    foreach($notLearnedLessons as &$notLearnedLesson) {
                        if(in_array($notLearnedLesson['id'], $learningsIds) ){
                            $notLearnedLesson['isLearned'] = 'learning';
                        }else{
                            $notLearnedLesson['isLearned'] = '';
                        }
                    }
                    $course['lessons'] = $notLearnedLessons;
                    $course['learnedLessonNum'] = count($finishIds);
                    $course['allLessonNum'] = $course['lessonNum'];
                }
            }
        }

        return $sortedCourses;
    }

    private function getClassroomStudyMissions()
    {
        $user = $this->getCurrentUser();

        $sortedClassrooms = array();

        $memberConditions = array(
            'userId' => $user->id,
            'locked' => 0,
            'role' => 'student'
        );
        $sort = array('createdTime','DESC');
        $classroomMems = $this->getClassroomService()->searchMembers($memberConditions,$sort,0,5);
        $classroomIds = ArrayToolkit::column($classroomMems,'classroomId');
        if(!empty($classroomIds)){
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

            foreach ($classroomMems as $member) {
                if (empty($classrooms[$member['classroomId']])) {
                    continue;
                }
                $classroom = $classrooms[$member['classroomId']];

                $sortedClassrooms[] = $classroom;
            }

            foreach ($sortedClassrooms as $key => &$classroom){
                $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
                if(!empty($courses)){
                    $courseIds = ArrayToolkit::column($courses,'id');
                    /**
                     * 找出学过的课时
                     */
                    $learnedConditions = array(
                        'userId' => $user->id,
                        'courseIds' => $courseIds
                    );
                    $sort = array( 'finishedTime','ASC');
                    $learnedCount = $this->getCourseService()->searchLearnCount($learnedConditions);
                    $learneds = $this->getCourseService()->searchLearns($learnedConditions,$sort,0,$learnedCount);
                    $learnedsGroupStatus = ArrayToolkit::group($learneds, 'status');

                    $finishs = isset($learnedsGroupStatus['finished']) ? $learnedsGroupStatus['finished'] : array();
                    $finishIds = ArrayToolkit::column($finishs, 'lessonId');

                    $learnings = isset($learnedsGroupStatus['learning']) ? $learnedsGroupStatus['learning'] : array();
                    $learningsIds = ArrayToolkit::column($learnings, 'lessonId');

                    $notLearnedConditions = array(
                        'status' => 'published',
                        'courseIds' => $courseIds,
                        'notLearnedIds' => $finishIds
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
                        unset($sortedClassrooms[$key]);
                    }else{
                        foreach($notLearnedLessons as &$notLearnedLesson) {
                            if(in_array($notLearnedLesson['id'], $learningsIds) ){
                                $notLearnedLesson['isLearned'] = 'learning';
                            }else{
                                $notLearnedLesson['isLearned'] = '';
                            }
                        }
                        $classroom['lessons'] = $notLearnedLessons;
                        $classroom['learnedLessonNum'] = count($finishIds);
                        $classroom['allLessonNum'] = $classroomLessonNum;
                    }

                }else{
                    unset($sortedClassrooms[$key]);
                }
            }
        }

        return $sortedClassrooms;
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