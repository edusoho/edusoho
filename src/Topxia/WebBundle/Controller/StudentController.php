<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\ExportHelp;
use Topxia\Common\SimpleValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\User\Impl\NotificationServiceImpl;

class StudentController extends BaseController
{
	public function showAction(Request $request, $userId)
    {
        $user             = $this->getUserService()->getUser($userId);
        $profile          = $this->getUserService()->getUserProfile($userId);
        $profile['title'] = $user['title'];

        $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        for ($i = 0; $i < count($userFields); $i++) {
            if (strstr($userFields[$i]['fieldName'], "textField")) {
                $userFields[$i]['type'] = "text";
            }

            if (strstr($userFields[$i]['fieldName'], "varcharField")) {
                $userFields[$i]['type'] = "varchar";
            }

            if (strstr($userFields[$i]['fieldName'], "intField")) {
                $userFields[$i]['type'] = "int";
            }

            if (strstr($userFields[$i]['fieldName'], "floatField")) {
                $userFields[$i]['type'] = "float";
            }

            if (strstr($userFields[$i]['fieldName'], "dateField")) {
                $userFields[$i]['type'] = "date";
            }
        }

        return $this->render('TopxiaWebBundle:Student:show-modal.html.twig', array(
            'user'       => $user,
            'profile'    => $profile,
            'userFields' => $userFields
        ));
    }

    public function definedShowAction(Request $request, $userId)
    {
        $profile = $this->getUserService()->getUserProfile($userId);

        $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        for ($i = 0; $i < count($userFields); $i++) {
            if (strstr($userFields[$i]['fieldName'], "textField")) {
                $userFields[$i]['type'] = "text";
            }

            if (strstr($userFields[$i]['fieldName'], "varcharField")) {
                $userFields[$i]['type'] = "varchar";
            }

            if (strstr($userFields[$i]['fieldName'], "intField")) {
                $userFields[$i]['type'] = "int";
            }

            if (strstr($userFields[$i]['fieldName'], "floatField")) {
                $userFields[$i]['type'] = "float";
            }

            if (strstr($userFields[$i]['fieldName'], "dateField")) {
                $userFields[$i]['type'] = "date";
            }
        }

        $course = $this->getSettingService()->get('course', array());

        $userinfoFields = array();

        if (isset($course['userinfoFields'])) {
            $userinfoFields = $course['userinfoFields'];
        }

        return $this->render('TopxiaWebBundle:Student:defined-show-modal.html.twig', array(
            'profile'        => $profile,
            'userFields'     => $userFields,
            'userinfoFields' => $userinfoFields
        ));
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
