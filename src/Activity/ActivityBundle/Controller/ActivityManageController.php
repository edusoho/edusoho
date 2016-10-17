<?php
namespace Activity\ActivityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class ActivityManageController extends BaseController
{
    public function activityModalAction(Request $request, $typeName)
    {
        $types = $this->getActivityService()->getActivityTypes();
        if (empty($types[$type])) {
            throw new $this->createInvalidArgumentException('activity type is invalid');
        }

        $type = $types[$typeName];

        return $this->render($type['create_modal'], array());
    }
}
