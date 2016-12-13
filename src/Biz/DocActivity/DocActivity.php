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

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $doc = $this->getFlashActivityDao()->get($activity['mediaId']);
        if($doc['finishType'] == 'time') {
            $result = $this->getActivityLearnLogService()->sumLearnedTimeByActivityId($activityId);
            return $result > $doc['finishDetail'];
        }

        if($doc['finishType'] == 'click') {
            $result = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'doc.finish');
            return !empty($result);
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