<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseController extends BaseController
{

    public function indexAction (Request $request)
    {
        $conditions = $request->query->all();
        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);
        $courses = $this->getCourseService()->searchCourses($conditions, null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));
  
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $courseSetting = $this->getSettingService()->get('course', array());
        if(!isset($courseSetting['live_course_enabled']))$courseSetting['live_course_enabled']="";

        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaAdminBundle:Course:index.html.twig', array(
            'conditions' => $conditions,
            'courses' => $courses ,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator,
            'liveSetEnabled' => $courseSetting['live_course_enabled'],
            'default'=> $default
        ));
    }

    private function searchFuncUsedBySearchActionAndSearchToFillBannerAction(Request $request,$twigToRender)
    {
        $key = $request->request->get("key");
        
        $conditions = array( "title"=>$key );
        $conditions['status'] = 'published';
        $conditions['type'] = 'normal';

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 6);

        $courses = $this->getCourseService()->searchCourses($conditions, null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));
  
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render($twigToRender, array(
            'key' => $key,
            'courses' => $courses,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator
        ));
    }

    public function searchAction(Request $request)
    {
        return $this->searchFuncUsedBySearchActionAndSearchToFillBannerAction($request,'TopxiaAdminBundle:Course:search.html.twig');
    }

    public function searchToFillBannerAction(Request $request)
    {
        return $this->searchFuncUsedBySearchActionAndSearchToFillBannerAction($request,'TopxiaAdminBundle:Course:search-to-fill-banner.html.twig');
    }

    public function deleteAction(Request $request, $id)
    {
        $result = $this->getCourseService()->deleteCourse($id);
        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id)
    {
        $this->getCourseService()->publishCourse($id);
        return $this->renderCourseTr($id);
    }

    public function closeAction(Request $request, $id)
    {
        $this->getCourseService()->closeCourse($id);
        return $this->renderCourseTr($id);
    }

    public function copyAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        return $this->render('TopxiaAdminBundle:Course:copy.html.twig', array(
            'course' => $course ,
        ));
    }

    public function copingAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        $conditions = $request->request->all();
        $course['title']=$conditions['title'];
        
        $newCourse = $this->getCourseCopyService()->copyCourse($course);
        
        $newTeachers = $this->getCourseCopyService()->copyTeachers($course['id'], $newCourse);

        $newChapters = $this->getCourseCopyService()->copyChapters($course['id'], $newCourse);

        $newLessons = $this->getCourseCopyService()->copyLessons($course['id'], $newCourse, $newChapters);

        $newQuestions = $this->getCourseCopyService()->copyQuestions($course['id'], $newCourse, $newLessons);

        $newTestpapers = $this->getCourseCopyService()->copyTestpapers($course['id'], $newCourse, $newQuestions);

        $this->getCourseCopyService()->convertTestpaperLesson($newLessons, $newTestpapers);
        
        $newMaterials = $this->getCourseCopyService()->copyMaterials($course['id'], $newCourse, $newLessons);
        
        $code = 'Homework';
        $homework = $this->getAppService()->findInstallApp($code);
        $isCopyHomework = $homework && version_compare($homework['version'], "1.0.4", ">=");

        if($isCopyHomework){
            $newHomeworks = $this->getCourseCopyService()->copyHomeworks($course['id'], $newCourse, $newLessons,$newQuestions);
            $newExercises = $this->getCourseCopyService()->copyExercises($course['id'], $newCourse, $newLessons);
        }

        return $this->redirect($this->generateUrl('admin_course'));
    }

    public function recommendAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        $ref = $request->query->get('ref');

        if ($request->getMethod() == 'POST') {
            $number = $request->request->get('number');

            $course = $this->getCourseService()->recommendCourse($id, $number);

            $user = $this->getUserService()->getUser($course['userId']);

            if ($ref == 'recommendList') {
                return $this->render('TopxiaAdminBundle:Course:course-recommend-tr.html.twig', array(
                    'course' => $course,
                    'user' => $user
                ));
            }


            return $this->renderCourseTr($id);
        }


        return $this->render('TopxiaAdminBundle:Course:course-recommend-modal.html.twig', array(
            'course' => $course,
            'ref' => $ref
        ));
    }

    public function cancelRecommendAction(Request $request, $id)
    {
        $course = $this->getCourseService()->cancelRecommendCourse($id);
        return $this->renderCourseTr($id);
    }

    public function recommendListAction(Request $request)
    {
        $conditions = array(
            'status' => 'published',
            'recommended'=> 1
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions),
            20
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'recommendedSeq',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render('TopxiaAdminBundle:Course:course-recommend-list.html.twig', array(
            'courses' => $courses,
            'users' => $users,
            'paginator' => $paginator
        ));
    }


    public function categoryAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:Category:embed', array(
            'group' => 'course',
            'layout' => 'TopxiaAdminBundle:Course:layout.html.twig',
        ));
    }

    public function dataAction(Request $request)
    {   
        $cond=array('type'=>'normal');

        $conditions = $request->query->all();

        $conditions=array_merge($cond,$conditions);
        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getCourseService()->searchCourses($conditions, null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        foreach ($courses as $key => $course) {
            $isLearnedNum=$this->getCourseService()->searchMemberCount(array('isLearned'=>1,'courseId'=>$course['id']));


            $learnTime=$this->getCourseService()->searchLearnTime(array('courseId'=>$course['id']));

            $lessonCount=$this->getCourseService()->searchLessonCount(array('courseId'=>$course['id']));
            
            $courses[$key]['isLearnedNum']=$isLearnedNum;
            $courses[$key]['learnTime']=$learnTime;
            $courses[$key]['lessonCount']=$lessonCount;

        }

        return $this->render('TopxiaAdminBundle:Course:data.html.twig', array(
            'courses'=>$courses,
            'paginator'=>$paginator,
        ));
    }

    public function lessonDataAction($id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        
        $lessons=$this->getCourseService()->searchLessons(array('courseId'=>$id),array('createdTime', 'ASC'),0,1000);

        foreach ($lessons as $key => $value) {
            $lessonLearnedNum=$this->getCourseService()->findLearnsCountByLessonId($value['id']);

            $finishedNum=$this->getCourseService()->searchLearnCount(array('status'=>'finished','lessonId'=>$value['id']));
            
            $lessonLearnTime=$this->getCourseService()->searchLearnTime(array('lessonId'=>$value['id']));
            $lessonLearnTime=$lessonLearnedNum==0 ? 0 : intval($lessonLearnTime/$lessonLearnedNum);

            $lessonWatchTime=$this->getCourseService()->searchWatchTime(array('lessonId'=>$value['id']));
            $lessonWatchTime=$lessonWatchTime==0 ? 0 : intval($lessonWatchTime/$lessonLearnedNum);

            $lessons[$key]['LearnedNum']=$lessonLearnedNum;
            $lessons[$key]['length']=intval($lessons[$key]['length']/60);
            $lessons[$key]['finishedNum']=$finishedNum;
            $lessons[$key]['learnTime']=$lessonLearnTime;
            $lessons[$key]['watchTime']=$lessonWatchTime;

            if($value['type']=='testpaper'){
                $paperId=$value['mediaId'];
                $score=$this->getTestpaperService()->searchTestpapersScore(array('testId'=>$paperId));
                $paperNum=$this->getTestpaperService()->searchTestpaperResultsCount(array('testId'=>$paperId));
                
                $lessons[$key]['score']=$finishedNum==0 ? 0 : intval($score/$paperNum);
            }
        }

        return $this->render('TopxiaAdminBundle:Course:lesson-data.html.twig', array(
            'course' => $course,
            'lessons'=>$lessons,
        ));
    }

    public function chooserAction (Request $request)
    {   
        $conditions = $request->query->all();

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getCourseService()->searchCourses($conditions, null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render('TopxiaAdminBundle:Course:course-chooser.html.twig', array(
            'conditions' => $conditions,
            'courses' => $courses ,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator
        ));
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function renderCourseTr($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaAdminBundle:Course:tr.html.twig', array(
            'user' => $this->getUserService()->getUser($course['userId']),
            'category' => $this->getCategoryService()->getCategory($course['categoryId']),
            'course' => $course ,
            'default'=>$default
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCourseCopyService()
    {
        return $this->getServiceKernel()->createService('Course.CourseCopyService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    private function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    private function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}