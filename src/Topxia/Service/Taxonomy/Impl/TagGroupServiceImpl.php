<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Taxonomy\TagService;

class TagGroupServiceImpl extends BaseService implements TagService
{
    private $allowFields = array(
        'name', 'scope', 'tagNum'
    );

    private $allowConditions = array(
        'name', 'scope', 'tagNum', 'createdTime', 'updatedTime'
    );

    public function get($id)
    {
        return $this->getTagGroupDao()->get($id);
    }

    public function search($conditions, $order, $start, $limit)
    {
        $this->fieterConditions($conditions);

        return $this->getTagGroupDao()->search($conditions, $order, $start, $limit);
    }

    public function create($fields)
    {
        $this->fieterFields($fields);

        $fields['createdTime'] = time();

        $tagGroup = $this->getTagGroupDao()->create($fields);

        $this->getLogService()->info('tagGroup', 'create', "添加标签组{$tagGroup['name']}(#{$tagGroup['id']})");

        return $tagGroup;
    }

    public function delete($id)
    {
        $this->getTagGroupDao()->delete($id);
        $this->getLogService()->info('tagGroup', 'delete', "删除标签组#{$id}");
    }

    public function update($id, $fields)
    {

        $tagGroup = $this->get($id);

        if (empty($tagGroup)) {
            throw $this->createServiceException("标签组(#{$id})不存在，更新失败！");
        }

        $this->fieterFields($fields);

        $fields['updatedTime'] = time();

        $this->getLogService()->info('tagGroup', 'update', "编辑标签组{$fields['name']}(#{$id})");
        return $this->getTagGroupDao()->update($id, $fields);
    }

    protected function fieterConditions($conditions)
    {
        return ArrayToolkit::parts($conditions, $this->allowConditions);
    }

    protected function fieterFields($fields)
    {
        return ArrayToolkit::parts($fields, $this->allowFields);
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getTagGroupDao()
    {
        $this->createDao('Taxonomy.TagGroupDao');
    }
}
