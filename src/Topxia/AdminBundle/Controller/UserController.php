<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\AdminBundle\Form\UserInfoType;

class UserController extends BaseController {

    public function indexAction (Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $searchForm = $this->createUserSearchForm();
        $searchForm->bind($request);
        
        $conditions = $searchForm->getData();
        
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('TopxiaAdminBundle:User:index.html.twig', array(
            'searchForm' => $searchForm->createView(),
            'users' => $users ,
            'paginator' => $paginator
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        return $this->render('TopxiaAdminBundle:User:show-modal.html.twig', array(
            'user' => $user,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $form = $this->createForm(new UserInfoType(), $user);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $info = $form->getData();
                $this->getUserService()->changeUserRoles($user['id'], $info['roles']);

            return $this->redirect($this->generateUrl('admin_user'));
            }
        }

        return $this->render('TopxiaAdminBundle:User:edit-modal.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
            'hash' => $this->makeHash($user),
        ));
    }

    private function createUserSearchForm()
    {
        return $this->createFormBuilder()
            ->add('roles', 'user_role', array(
                'required' => false,
                'empty_value' => '--用户组--',
            ))
            ->add('keywordType', 'choice', array(
                'choices' => array(
                    'nickname' => '用户名',
                    'email' => '邮件地址',
                    'loginIp' => '登录IP',
                ),
            ))
            ->add('keyword', 'text', array('required' => false))
            ->getForm();
    }

    public function lockAction($id)
    {
        $this->getUserService()->lockUser($id);

        return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
            'user' => $this->getUserService()->getUser($id),
        ));
    }

    public function unlockAction($id)
    {
        $this->getUserService()->unlockUser($id);
        
        return $this->render('TopxiaAdminBundle:User:user-table-tr.html.twig', array(
            'user' => $this->getUserService()->getUser($id),
        ));
    }

    public function emailSendAction(Request $request, $id, $hash)
    {
        $user = $this->checkHash($id, $hash);
        if (empty($user)) {
            return $this->createJsonResponse(false);
        }

        $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'));

        $this->sendEmail(
            $user['email'],
            "请激活你的帐号，完成注册",
            $this->renderView('TopxiaWebBundle:Register:email-verify.txt.twig', array(
                'user' => $user,
                'token' => $token,
            ))
        );
        return $this->createJsonResponse(true);
    }

    private function makeHash($user)
    {
        $string = $user['id'] . $user['email'] . $this->container->getParameter('secret');
        return md5($string);
    }

    private function checkHash($userId, $hash)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            return false;
        }

        if ($this->makeHash($user) !== $hash) {
            return false;
        }

        return $user;
    }


}