<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseQuestionController extends BaseController
{

    public function indexAction (Request $request, $postStatus)
    {

		$conditions = $request->query->all(); 
        if ( isset($conditions['keywordType']) && $conditions['keywordType'] == 'courseTitle'){
            $courses = $this->getCourseService()->findCoursesByLikeTitle(trim($conditions['keyword']));
            $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');
            if (count($conditions['courseIds']) == 0){
                return $this->render('TopxiaAdminBundle:CourseQuestion:index.html.twig', array(
                    'paginator' =>  new Paginator($request,0,20),
                    'questions' => array(),
                    'users'=> array(),
                    'courses' => array(),
                    'lessons' => array(),
                    'type' => $postStatus                    
                ));
            }  
        }               
        $conditions['type'] = 'question';
        // if($postStatus == 'unPosted'){
        //     $conditions['postNum'] = 0;
        // }

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );

        $questions = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questions =ArrayToolkit::index($questions,'id');
        // var_dump($questions);exit();

        $unPostedQuestion = array();
        $threadPosts = array();
        $threadUserId =array();
        foreach ($questions as $key => $value) {
            $threadPosts[$value['id']] = $this->getThreadService()->findThreadsPostByThreadId($key);
            $threadUserId[$value['id']] = array($value['userId']);
            // if($value['userId'] == $value['latestPostUserId'] && $value['postNum'] == 1){
            //     $unPostedQuestion[] = $value;
            // }
            // $threadPosts[] = ArrayToolkit::index($threadPosts,'threadId');
        }
        // var_dump($threadUserId);exit();

        foreach ($threadPosts as $key => $value) {
            // var_dump($key);
            foreach ($threadUserId as $a => $b) {
                if($key == $a){
                    // var_dump($key);
                    // var_dump($a);
                    var_dump($value);
                    var_dump($b);
                     // $difference = array_diff($value, $b);
                }
            }
            // $a =ArrayToolkit::column($value,'userId');
            // var_dump($a);
        }
// var_dump($difference);
exit();
        if($postStatus == 'unPosted'){
            $conditions['postNum'] = 0;
        }

         $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );

        $questions = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
          if($postStatus == 'unPosted'){
                $questions = array_merge($questions,$unPostedQuestion);
          }
var_dump($questions);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($questions, 'lessonId'));

    	return $this->render('TopxiaAdminBundle:CourseQuestion:index.html.twig', array(
    		'paginator' => $paginator,
            'questions' => $questions,
            'users'=> $users,
            'courses' => $courses,
            'lessons' => $lessons,
            'type' => $postStatus
    	));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getThreadService()->deleteThread($id);
        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids ? : array() as $id) {
            $this->getThreadService()->deleteThread($id);
        }
        return $this->createJsonResponse(true);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}