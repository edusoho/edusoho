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

        $media                       = $this->parseMedia($fields['media']);
        $updateFields['mediaId']     = $media['id'];
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

    protected function parseMedia($media)
    {
        $media = json_decode($media, JSON_OBJECT_AS_ARRAY);
        return $media;
    }

    /**
     * @return PptActivityDao
     */
    protected function getPptActivityDao()
    {
        return $this->getBiz()->dao('PptActivity:PptActivityDao');
    }

}