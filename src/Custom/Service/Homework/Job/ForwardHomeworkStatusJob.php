<?php
namespace Custom\Service\Homework\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class ForwardHomeworkStatusJob implements Job
{
	public function execute($params)
    {
    	$this -> getHomeworkService() -> forwardHomeworkStatus();
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework.HomeworkService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}
