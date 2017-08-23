<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\Service\PushService;
use Topxia\Api\Util\MobileSchoolUtil;

class PushServiceImpl extends BaseService implements PushService
{
    public function push()
    {
        // TODO: Implement push() method.
    }

    public function pushArticleCreate($event)
    {
        $schoolUtil = new MobileSchoolUtil();
        $articleApp = $schoolUtil->getArticleApp();
        $articleApp['avatar'] = $this->getAssetUrl($articleApp['avatar']);
        $articleApp['app'] = $schoolUtil->findSchoolAppById($s);

    }
}
