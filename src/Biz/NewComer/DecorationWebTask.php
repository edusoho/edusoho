<?php

namespace Biz\NewComer;

use Biz\Content\Service\BlockService;
use Biz\Course\Service\CourseSetService;

class DecorationWebTask extends BaseNewcomer
{
    public function getStatus()
    {
        $this->setDecorationWebTaskResult();
        $newcomerTask = $this->getSettingService()->get('newcomer_task', array());

        if (!empty($newcomerTask['decoration_web_task']['status'])) {
            return true;
        }

        return false;
    }

    public function setDecorationWebTaskResult()
    {
        $newcomerTask = $this->getSettingService()->get('newcomer_task', array());
        $decorationWebTask = isset($newcomerTask['decoration_web_task']) ? $newcomerTask['decoration_web_task'] : array();

        $recommendCount = $this->getCourseSetService()->countCourseSets(array('recommended' => 1));
        if (!empty($recommendCount)) {
            $decorationWebTask['child_task']['recommend_course'] = 1;
        }

        $latestBlockHistory = $this->getBlockService()->getLatestBlockHistory();
        if (!empty($latestBlockHistory)) {
            $decorationWebTask['child_task']['set_banner'] = 1;
        }

        if (!empty($newcomerTask['decoration_web_task']['child_task']['top_navigation'])) {
            $decorationWebTask['child_task']['set_top_navigation'] = 1;
        }

        //完成推荐课程、设置轮播图、设置顶部导航三步骤 算该任务完成
        if (isset($decorationWebTask['child_task']) && 3 === count($decorationWebTask['child_task'])) {
            $decorationWebTask['status'] = 1;
        }

        $newcomerTask = array_merge($newcomerTask, array('decoration_web_task' => $decorationWebTask));

        return $this->getSettingService()->set('newcomer_task', $newcomerTask);
    }

    /**
     * @return BlockService
     */
    protected function getBlockService()
    {
        return $this->getBiz()->service('Content:BlockService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
