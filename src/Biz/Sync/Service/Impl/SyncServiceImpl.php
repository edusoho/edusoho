<?php

namespace Biz\Sync\Service\Impl;

use Biz\BaseService;
use Biz\Sync\Service\SychronizerFactory;
use Biz\Sync\Service\SyncService;

class SyncServiceImpl extends BaseService implements SyncService
{
    public function sync($action, $sourceId)
    {
        list($alias, $method) = explode('.', $action);

        $syncObject = SychronizerFactory::create($alias);
        $syncObject->setBiz($this->biz);

        call_user_func(array($syncObject, $method), $sourceId);
        call_user_func(array($syncObject, 'flush'));
    }
}
