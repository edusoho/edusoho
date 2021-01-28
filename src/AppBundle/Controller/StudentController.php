<?php

namespace AppBundle\Controller;

use Biz\System\Service\SettingService;
use Biz\User\Service\UserFieldService;
use Symfony\Component\HttpFoundation\Request;

class StudentController extends BaseController
{
    public function showAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $profile = $this->getUserService()->getUserProfile($userId);
        $profile['title'] = $user['title'];

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0, $iMax = count($userFields); $i < $iMax; ++$i) {
            if (strstr($userFields[$i]['fieldName'], 'textField')) {
                $userFields[$i]['type'] = 'text';
            }

            if (strstr($userFields[$i]['fieldName'], 'varcharField')) {
                $userFields[$i]['type'] = 'varchar';
            }

            if (strstr($userFields[$i]['fieldName'], 'intField')) {
                $userFields[$i]['type'] = 'int';
            }

            if (strstr($userFields[$i]['fieldName'], 'floatField')) {
                $userFields[$i]['type'] = 'float';
            }

            if (strstr($userFields[$i]['fieldName'], 'dateField')) {
                $userFields[$i]['type'] = 'date';
            }
        }

        return $this->render('student/show-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'userFields' => $userFields,
        ));
    }

    public function definedShowAction(Request $request, $userId)
    {
        $profile = $this->getUserService()->getUserProfile($userId);

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0, $iMax = count($userFields); $i < $iMax; ++$i) {
            if (strstr($userFields[$i]['fieldName'], 'textField')) {
                $userFields[$i]['type'] = 'text';
            }

            if (strstr($userFields[$i]['fieldName'], 'varcharField')) {
                $userFields[$i]['type'] = 'varchar';
            }

            if (strstr($userFields[$i]['fieldName'], 'intField')) {
                $userFields[$i]['type'] = 'int';
            }

            if (strstr($userFields[$i]['fieldName'], 'floatField')) {
                $userFields[$i]['type'] = 'float';
            }

            if (strstr($userFields[$i]['fieldName'], 'dateField')) {
                $userFields[$i]['type'] = 'date';
            }
        }

        $course = $this->getSettingService()->get('course', array());

        $userinfoFields = array();

        if (isset($course['userinfoFields'])) {
            $userinfoFields = $course['userinfoFields'];
        }

        return $this->render('student/defined-show-modal.html.twig', array(
            'profile' => $profile,
            'userFields' => $userFields,
            'userinfoFields' => $userinfoFields,
        ));
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
