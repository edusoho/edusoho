<?php
namespace Topxia\Service\Sale\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sale\OffsaleService;
use Topxia\Common\ArrayToolkit;

class OffsaleServiceImpl extends BaseService implements OffsaleService
{

    public function getOffsale($id)
    {
        return $this->getOffsaleDao()->getOffsale($id);
    }

    public function getOffsaleByCode($code)
    {
        return $this->getOffsaleDao()->getOffsaleByCode($code);
    }

    public function findOffsalesByIds(array $ids)
    {
        $orders = $this->getOffsaleDao()->findOffsalesByIds($ids);
        return ArrayToolkit::index($orders, 'id');
    }


    public function createOffsale($offsale){



    }


    public function isCodeAvaliable($code){

        if (empty($code)) {
            return false;
        }
        $offsale = $this->getOffsaleDao()->getOffsaleByCode($code);
        return $offsale;

    }




    private function generateOffsaleCode($order)
    {
        return  'CF' . date('YmdHis', time()) . mt_rand(10000,99999);
    }


    private function getOffsaleDao()
    {
        return $this->createDao('Sale.OffsaleDao');
    }

   

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}