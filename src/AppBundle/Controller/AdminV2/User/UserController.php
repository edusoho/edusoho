<?php

namespace AppBundle\Controller\AdminV2\User;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\User\Service\UserFieldService;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    public function showAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $profile = $this->getUserService()->getUserProfile($id);
        $profile['title'] = $user['title'];

        $fields = $this->getFields();

        return $this->render('admin-v2/user/user/show-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
            'fields' => $fields,
        ));
    }

    protected function getFields()
    {
        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0; $i < count($fields); ++$i) {
            if (strstr($fields[$i]['fieldName'], 'textField')) {
                $fields[$i]['type'] = 'text';
            }

            if (strstr($fields[$i]['fieldName'], 'varcharField')) {
                $fields[$i]['type'] = 'varchar';
            }

            if (strstr($fields[$i]['fieldName'], 'intField')) {
                $fields[$i]['type'] = 'int';
            }

            if (strstr($fields[$i]['fieldName'], 'floatField')) {
                $fields[$i]['type'] = 'float';
            }

            if (strstr($fields[$i]['fieldName'], 'dateField')) {
                $fields[$i]['type'] = 'date';
            }
        }

        return $fields;
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }
}
