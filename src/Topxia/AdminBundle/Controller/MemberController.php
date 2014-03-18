<?php
namespace Topxia\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class MemberController extends BaseController
{

	public function indexAction(Request $request) 
	{
		$fields = $request->query->all();
        $conditions = array(
            'nickname'=>'',
            'level'=>''
        );
        if(!empty($fields)){
            $conditions =$fields;
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getMemberService()->searchMembersCount($conditions),
            20
        );
        var_dump($paginator);exit();
        $users = $this->getMemberService()->searchMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('TopxiaAdminBundle:Member:index.html.twig', array(
            'users' => $users ,
            'paginator' => $paginator
        ));
	}

    public function updateAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $formData = $request->request->all();
            $userData['nickname'] = $formData['nickname'];
            $userData['deadline'] = $formData['deadline'];
            $userData['memberLevel'] = $formData['memberLevel'];
            $user = $this->getMemberService()->updateMemberLevel($userData);

            $this->getLogService()->info('user', 'add', "管理员添加新会员 {$user['nickname']} ({$user['id']})");

            return $this->redirect($this->generateUrl('admin_member'));
        }
        return $this->render('TopxiaAdminBundle:Member:update-model.html.twig');
    }

    public function nicknameCheckAction( Request $request )
    {
        $nickname = $request->query->get('value');
        list($result, $message) = $this->getMemberService()->checkMemberName($nickname);
        if ($result == 'success') {
            $response = array('success' => true, 'message' => '该昵称可以使用');
        } else {
            $response = array('success' => false, 'message' => $message);
        }
        return $this->createJsonResponse($response);
    }

    protected function getMemberService()
    {
        return $this->getServiceKernel()->createService('User.MemberService');
    }

}


