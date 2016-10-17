<?php
namespace Codeages\PluginBundle\Biz\Service\Impl;

use Codeages\PluginBundle\Biz\Service\AppService;
use Codeages\Biz\Framework\Service\BaseService;

class AppServiceImpl extends BaseService implements AppService
{
    public function getApp($id)
    {
        return $this->getAppDao()->get($id);
    }

    public function installPluginApp($code)
    {
        
    }

    protected function getAppDao()
    {
        return $this->biz->dao('CodeagesPluginBundle:AppDao');
    }

}