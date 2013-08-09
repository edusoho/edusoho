<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ReviewController extends BaseController {

    public function indexAction (Request $request)
    {   
        
        $form = $this->createFormBuilder()
            ->add('keywordType', 'choice', array(
                    'choices'   => array('title' => '标题', 'content' => '内容'),
                    'required'  => false,
                    'empty_value' => '关键词类型',
                ))
            ->add('keyword', 'text', array('required' => false))
            ->add('nickname', 'text', array('required' => false))
            ->getForm();
        $form->bind($request);

        $conditions = $form->getData();
        $convertedConditions = $this->convertConditions($conditions);
        $paginator = new Paginator(
            $request,
            $this->getReviewService()->searchReviewsCount($convertedConditions),
            20
        );


        $reviews = $this->getReviewService()->searchReviews(
            $convertedConditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        ); 

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'courseId'));
        return $this->render('TopxiaAdminBundle:Review:index.html.twig',array(
            'form' => $form->createView(),
            'paginator' => $paginator,
            'reviews' => $reviews,
            'users'=>$users,
            'courses'=>$courses
            ));
    }

    public function deleteChoosedReviewsAction(Request $request)
    {  
        $ids = $request->request->get('ids');
        $result = $this->getReviewService()->deleteReviewsByIds($ids);
        if($result){
           return $this->createJsonResponse(array("status" =>"success")); 
       } else {
           return $this->createJsonResponse(array("status" =>"failed")); 
       }
    }

    private function convertConditions($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            if (empty($user)) {
                throw $this->createNotFoundException(sprintf("昵称为%s的用户不存在", $conditions['nickname']));
            }
            $conditions['userId'] = $user['id'];
        }

        unset($conditions['nickname']);

        if(empty($conditions['keywordType'])){
            unset($conditions['keyword']);
        }

        return $conditions;
    }
    
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }


}