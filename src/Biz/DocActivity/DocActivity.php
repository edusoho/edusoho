<?php


namespace Biz\DocActivity;


use Biz\Activity\Config\Activity;
use Biz\DocActivity\Dao\DocActivityDao;
use Topxia\Common\ArrayToolkit;


class DocActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '文档',
            'icon' => 'es-icon es-icon-description'
        );
    }
    
    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:DocActivity:create',
            'edit'   => 'WebBundle:DocActivity:edit',
            'show'   => 'WebBundle:DocActivity:show'
        );
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function create($fields)
    {
        $doc = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $biz                  = $this->getBiz();
        $doc['createdUserId'] = $biz['user']['id'];
        $doc['createdTime']   = time();

        $doc = $this->getDocActivityDao()->create($doc);
        return $doc;
    }

    public function canFinish($activityId)
    {
        $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
        $activity = $this->getActivityService()->getActivity($activityId);
        $doc = $this->getDocActivityDao()->get($activity['mediaId']);
        if(!empty($result)) {
            if($doc['finishType'] == 'time') {
                return $result > $doc['finishDetail'];
            }
            return true;
        }
        return false;
    }

    public function update($targetId, $fields)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $updateFields['updatedTime'] = time();
        return $this->getDocActivityDao()->update($targetId, $updateFields);
    }

    public function delete($targetId)
    {
        return $this->getDocActivityDao()->delete($targetId);
    }

    public function get($targetId)
    {
        return $this->getDocActivityDao()->get($targetId);
    }

    /**
     * @return DocActivityDao
     */
    protected function getDocActivityDao()
    {
        return $this->getBiz()->dao('DocActivity:DocActivityDao');
    }
    
}