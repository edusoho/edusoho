<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class StudentController extends BaseController 
{
    public function indexAction (Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'role'=>'ROLE_USER',
            'keywordType'=>'',
            'keyword'=>''
        );

        if(!empty($fields)){
            $conditions =$fields;
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('TopxiaAdminBundle:User:index.html.twig', array(
            'users' => $users ,
            'paginator' => $paginator
        ));
    }
    
}