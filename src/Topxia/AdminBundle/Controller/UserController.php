<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Form\UserProfileType;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

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

    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];

        $form = $this->createForm(new UserProfileType(), $profile);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $profile = $form->getData();
                $this->getUserService()->updateUserProfile($user['id'], $profile);
                return $this->redirect($this->generateUrl('settings'));
            }
        }

        return $this->render('TopxiaAdminBundle:User:edit-modal.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
            'profile'=>$profile
        ));
    }

    public function showAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $profile = $this->getUserService()->getUserProfile($id);
        $profile['title'] = $user['title'];

        return $this->render('TopxiaAdminBundle:User:show-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
        ));
    }

    public function setRolesAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if ($request->getMethod() == 'POST') {
            $userRoles = $request->request->get('role');
            $this->getUserService()->changeUserRoles($user['id'], $userRoles);
            return $this->redirect($this->generateUrl('admin_user'));
        }

        return $this->render('TopxiaAdminBundle:User:set-roles-modal.html.twig', array(
            'user' => $user
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

    public function sendPasswordResetEmail(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        if (empty($user)) {
            throw $this->createNotFoundException();
        }

        $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
        $this->sendEmail(
            $user['email'],
            "重设{$user['nickname']}在{$this->setting('site.name', 'EDUSOHO')}的密码",
            $this->renderView('TopxiaWebBundle:PasswordReset:reset.txt.twig', array(
                'user' => $user,
                'token' => $token,
            )), 'html'
        );

        return $this->createJsonResponse(true);
    }

    public function sendEmailVerifyEmailAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        if (empty($user)) {
            throw $this->createNotFoundException();
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

    public function logsAction(Request $request)
    {
        $searchForm = $this->createLogSearchForm();
        $searchForm->bind($request);
        $conditions = $searchForm->getData();  

        $paginator = new Paginator(
            $this->get('request'),
            $this->getLogService()->searchLogCount($conditions),
            30
        );

        $logs = $this->getLogService()->searchLogs(
            $conditions, 
            'created', 
            $paginator->getOffsetCount(), 
            $paginator->getPerPageCount()
        );
        
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($logs, 'userId'));

        return $this->render('TopxiaAdminBundle:User:logs.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
            'form' => $searchForm->createView(),
            'users' => $users
        ));
    }

    protected function createLogSearchForm() {
        $form = $this->createFormBuilder()
                ->add('startDateTime', 'text',array(
                    'required' => false
                ))
                ->add('endDateTime', 'text', array(
                    'required' => false
                ))
                ->add('level', 'choice', array(
                    'choices'   => array(
                        '' => '日志等级',
                        'info' => '提示', 
                        'warning' => '警告', 
                        'error' => '错误'
                    ),
                    'required'  => false,
                ))
                ->add('nickname', 'text', array(
                    'required' => false
                ))
                ->getForm();

        return $form;
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');        
    }

}