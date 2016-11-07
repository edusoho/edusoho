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
        $ppt = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail'
        ));

        $media          = $this->parseMedia($fields['media']);
        $ppt['mediaId'] = $media['id'];

        $biz                  = $this->getBiz();
        $ppt['createdUserId'] = $biz['user']['id'];
        $ppt['createdTime']   = time();

        $ppt = $this->getDocActivityDao()->create($ppt);
        return $ppt;
    }

    public function update($targetId, $fields)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'finishType',
            'finishDetail',
        ));

        $media                       = $this->parseMedia($fields['media']);
        $updateFields['mediaId']     = $media['id'];
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

    protected function parseMedia($media)
    {
        $media = json_decode($media, JSON_OBJECT_AS_ARRAY);
        return $media;
    }

    /**
     * @return DocActivityDao
     */
    protected function getDocActivityDao()
    {
        return $this->getBiz()->dao('DocActivity:DocActivityDao');
    }
    
}