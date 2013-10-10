<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\TimeUtils;


class PhotoCommentController extends BaseController
{

	public function indexAction(Request $request,$id){

        $feild['id']=$id;
        $comments=$this->getPhotoService()->findCommentsByFileId($feild,'latest',0,100);
        $userids=ArrayToolkit::column($comments,'userId');
        $users=$this->getUserService()->findUsersByIds($userids);
		return $this->render('TopxiaWebBundle:PhotoComment:commit-list.html.twig',array(
            'comments'=>$comments,
            'users'=>$users));
	}

    public function createAction(Request $request,$id){
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }
        
         $form = $this->createActivityForm();
         if ($request->getMethod() == 'POST') {
            $form->bind($request); 
                $fields = $form->getData();
                $fields['imgId']=$id;
                $vals=$fields;
                $qustion=$this->getPhotoService()->addComment($vals);
                $comments=array();
                $users=array();
                $comments[]=$qustion;
                $user=$this->getUserService()->getUser($qustion['userId']);
                $users[$user['id']]=$user;
                return $this->render('TopxiaWebBundle:PhotoComment:commit-list.html.twig',array(
                'comments'=>$comments,
                'users'=>$users));
        }

    }

    private function createActivityForm()
    {
        return $this->createNamedFormBuilder('qustion')
            ->add('content', 'text')
            ->getForm();
    }

    public function deleteAction(Request $request,$id){

        return $this->createJsonResponse(false);  
    }


    private function getPhotoService()
    {
        return $this->getServiceKernel()->createService('Photo.PhotoService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}




