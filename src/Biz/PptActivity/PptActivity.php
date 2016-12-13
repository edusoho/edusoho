<?php


namespace Biz\PptActivity;


use Biz\Activity\Config\Activity;
use Biz\PptActivity\Dao\PptActivityDao;
use Topxia\Common\ArrayToolkit;

class PptActivity extends Activity
{
    public function registerActions()
    {
        return array(
            'edit'   => 'WebBundle:PptActivity:edit',
            'show'   => 'WebBundle:PptActivity:show',
            'create' => 'WebBundle:PptActivity:create'
        );
    }

    protected function registerListeners()
    {

    }

    public function getMetas()
    {
        return array(
            'name' => 'PPT',
            'icon' => 'es-icon es-icon-pptclass'
        );
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $ppt = $this->getPptActivityDao()->get($activity['mediaId']);
        
        if($ppt['finishType'] == 'time') {
            $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
            return !empty($result) && $result > $ppt['finishDetail'];
        }

        if($ppt['finishType'] == 'end') {
            $result = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'ppt.finished');
            return !empty($result);
        }
        return false;
    }

    public function create($fields)
    {
        $ppt = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $biz                  = $this->getBiz();
        $ppt['createdUserId'] = $biz['user']['id'];
        $ppt['createdTime']   = time();

        $ppt = $this->getPptActivityDao()->create($ppt);
        return $ppt;
    }

    public function update($targetId, $fields)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $updateFields['updatedTime'] = time();
        return $this->getPptActivityDao()->update($targetId, $updateFields);
    }

    public function delete($targetId)
    {
        return $this->getPptActivityDao()->delete($targetId);
    }

    public function get($targetId)
    {
        return $this->getPptActivityDao()->get($targetId);
    }

    /**
     * @return PptActivityDao
     */
    protected function getPptActivityDao()
    {
        return $this->getBiz()->dao('PptActivity:PptActivityDao');
    }

}