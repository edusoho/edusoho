<?php
namespace Custom\Service\Order\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class ForwardHomeworkStatusJob implements Job
{
	public function execute($params)
    {
    	$this -> getHomeworkService() -> forwardHomeworkStatus();
    }

    protected function getHomewokService()
    {
        return $this->getServiceKernel()->createService('Custom:Homework.HomeworkService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}
