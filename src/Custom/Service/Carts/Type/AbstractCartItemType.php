<?php

namespace Custom\Service\Carts\Type;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceKernel;

abstract class AbstractCartItemType extends BaseService
{
    abstract public function getItemsAndExtra($itemIds, $extraParams);

    public function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    public function getUserService()
    {
    	return $this->getServiceKernel()->createService('User.UserService');
    }
}