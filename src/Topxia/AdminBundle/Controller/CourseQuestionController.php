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

        $unPostedQuestion = array();
        $threadPosts = array();
        $threadUserId =array();
        foreach ($questions as $key => $value) {
            $threadPosts[$value['id']] = $this->getThreadService()->findThreadsPostByThreadId($value['id']);
            $postUserIdCount = $this->getThreadService()->getPostUserIdCountByThreadId($value['id']);
            $threadUserId['userId']=$value['userId'];
            if ($value['postNum'] == 0) {
                $unPostedQuestion[] = $value;
            }else{
                foreach ($threadPosts as $threadKey => $threadValue) {
                    if(in_array($threadUserId,$threadValue) && $postUserIdCount == 1 && !(in_array($value, $unPostedQuestion))){
                        $unPostedQuestion[] = $value;
                    }
                }
            }
        }
          if($postStatus == 'unPosted'){
                $questions = $unPostedQuestion;
          }

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