<?php
namespace Custom\Service\Taxonomy\Impl;

use Custom\Service\Taxonomy\ColumnService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class ColumnServiceImpl extends BaseService implements ColumnService
{

    public function getColumn($id)
    {
        return $this->getColumnDao()->getColumn($id);
    }

    public function getColumnByName($name)
    {
        return $this->getColumnDao()->getColumnByName($name);
    }
    

    public function getColumnByLikeName($name)
    {
        return $this->getColumnDao()->getColumnByLikeName($name);
    }

    public function findAllColumns($start, $limit)
    {
    	return $this->getColumnDao()->findAllColumns($start, $limit);
    }

    public function getAllColumnCount()
    {
        return $this->getColumnDao()->findAllColumnsCount();
    }

    public function findColumnsByIds(array $ids)
    {
    	return $this->getColumnDao()->findColumnsByIds($ids);
    }

    public function findColumnsByNames(array $names)
    {
    	return $this->getColumnDao()->findColumnsByNames($names);
    }

    public function isColumnNameAvalieable($name, $exclude=null)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $column = $this->getColumnByName($name);

        return $column ? false : true;
    }

    public function addColumn(array $column)
    {
        $column = ArrayToolkit::parts($column, array('name','description'));

        $this->filterColumnFields($column);
        $column['createdTime'] = time();

        $column = $this->getColumnDao()->addColumn($column);

        $this->getLogService()->info('column', 'create', "添加专栏{$column['name']}(#{$column['id']})");

        return $column;
    }

    public function updateColumn($id, array $fields)
    {
        $column = $this->getColumn($id);
        if (empty($column)) {
            throw $this->createServiceException("专栏(#{$id})不存在，更新失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('name','description'));
        $this->filterColumnFields($fields, $column);

        $this->getLogService()->info('column', 'update', "编辑专栏{$fields['name']}(#{$id})");

        return $this->getColumnDao()->updateColumn($id, $fields);
    }

    public function deleteColumn($id)
    {
        $this->getColumnDao()->deleteColumn($id);

        $this->getLogService()->info('column', 'delete', "删除专栏#{$id}");
    }

    private function filterColumnFields(&$column, $relatedColumn = null)
    {
        if (empty($column['name'])) {
            throw $this->createServiceException('标签名不能为空，添加失败！');
        }

        $column['name'] = (string) $column['name'];

        $exclude = $relatedColumn ? $relatedColumn['name'] : null;
        if (!$this->isColumnNameAvalieable($column['name'], $exclude)) {
            throw $this->createServiceException('该标签名已存在，添加失败！');
        }

        return $column;
    }

    public function changeColumnAvatar($columnId, $filePath, array $options)
    {
        $column = $this->getColumn($columnId);
        if (empty($column)) {
            throw $this->createServiceException('标签不存在，图标更新失败！');
        }


        $pathinfo = pathinfo($filePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $largeImage = $rawImage->copy();
        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        $largeImage->resize(new Box(200, 200));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 90));
        $largeFileRecord = $this->getFileService()->uploadFile('course', new File($largeFilePath));

        $largeImage->resize(new Box(120, 120));
        $mediumFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_medium.{$pathinfo['extension']}";
        $largeImage->save($mediumFilePath, array('quality' => 90));
        $mediumFileRecord = $this->getFileService()->uploadFile('course', new File($mediumFilePath));

        $largeImage->resize(new Box(48, 48));
        $smallFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_small.{$pathinfo['extension']}";
        $largeImage->save($smallFilePath, array('quality' => 90));
        $smallFileRecord = $this->getFileService()->uploadFile('course', new File($smallFilePath));
        @unlink($filePath);

        $oldAvatars = array(
            'smallAvatar' => $column['smallAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $column['smallAvatar']) : null,
            'mediumAvatar' => $column['mediumAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $column['mediumAvatar']) : null,
            'largeAvatar' => $column['largeAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $column['largeAvatar']) : null
        );

        array_map(function($oldAvatar){
            if (!empty($oldAvatar)) {
                @unlink($oldAvatar);
            }
        }, $oldAvatars);

        return  $this->getColumnDao()->updateColumn($columnId, array(
            'smallAvatar' => $smallFileRecord['uri'],
            'mediumAvatar' => $mediumFileRecord['uri'],
            'largeAvatar' => $largeFileRecord['uri'],
        ));
    }




        private function getColumnDao()
        {
            return $this->createDao('Custom:Taxonomy.ColumnDao');
        }

        private function getLogService()
        {
            return $this->createService('System.LogService');
        }
         private function getFileService()
        {
            return $this->createService('Content.FileService');
        }

}  