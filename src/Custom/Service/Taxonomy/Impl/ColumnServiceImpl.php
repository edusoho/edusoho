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
        
        return ColumnSerialize::unserializes($this->getColumnDao()->getColumn($id));
    }
    public function getColumnByCode($code){
         return $this->getColumnDao()->getColumnByCode($code);
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
        $column = ArrayToolkit::parts($column, array('name','subtitle','code','weight','classIndex','description'));
        $this->filterColumnFields($column);
        $code = $column['code'];
        
        $resultCode = $this->getColumnByCode($code);
        if(!empty($resultCode)){
             throw $this->createServiceException('专栏编码已经存在，添加失败！');
        }
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

        $fields = ArrayToolkit::parts($fields, array('name','subtitle','weight','classIndex','description','lowTagIds','middleTagIds','highTagIds','code'));
        $this->filterColumnFields($fields, $column);

        $fields['lowTagIds'] = array_filter($fields['lowTagIds']);
        $fields['middleTagIds'] = array_filter($fields['middleTagIds']);
        $fields['highTagIds'] = array_filter($fields['highTagIds']);

        $this->getLogService()->info('column', 'update', "编辑专栏{$fields['name']}(#{$id})");

        return $this->getColumnDao()->updateColumn($id, ColumnSerialize::serialize($fields));
    }

    public function deleteColumn($id)
    {
        $this->getColumnDao()->deleteColumn($id);

        $this->getLogService()->info('column', 'delete', "删除专栏#{$id}");
    }

    private function filterColumnFields(&$column)
    {
        if (empty($column['name'])) {
            throw $this->createServiceException('专栏名不能为空，添加失败！');
        }
        if (empty($column['code'])) {
            throw $this->createServiceException('专栏编码不能为空，添加失败！');
        }
        if (empty($column['weight'])) {
            throw $this->createServiceException('专栏排序不能为空，添加失败！');
        }
        if (empty($column['classIndex'])) {
            throw $this->createServiceException('专栏样式不能为空，添加失败！');
        }
       
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
    public function findColumnsByIds(array $ids)
    {
        return $this->getColumnDao()->findColumnsByIds($ids);
    }
    public function findColumnsByNames(array $names)
    {
        return $this->getColumnDao()->findColumnsByNames($names);
    }
    public function findTagIdsByColumnIdAndCourseComplexity($columId,$courseComplexity){
        $result = array();
        $tagIds = $this->getColumnDao()->findTagIdsByColumnIdAndCourseComplexity($columId,$courseComplexity);
        if($tagIds){
            foreach ($tagIds as $key => $value) {
                foreach ($value as $k => $v) {
                   $temp = explode('|', trim($v, '|'));
                   $result = array_merge($result, $temp);
                }
            }
        
        }
        
        return $this->getTagService()->findTagsByIds($result);
     
    }
   



        private function getTagService()
        {
            return $this->createService('Taxonomy.TagService');
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

class ColumnSerialize
{


    public static function serialize(array &$column)
    {

        if (isset($column['lowTagIds'])) {
            if (is_array($column['lowTagIds']) and !empty($column['lowTagIds'])) {
                $column['lowTagIds'] = '|' . implode('|', $column['lowTagIds']) . '|';
            
            } else {
                $column['lowTagIds'] = '';
            }
        }
        if (isset($column['middleTagIds'])) {
            if (is_array($column['middleTagIds']) and !empty($column['middleTagIds'])) {
                $column['middleTagIds'] = '|' . implode('|', $column['middleTagIds']) . '|';
            } else {
                $column['middleTagIds'] = '';
            }
        }
        if (isset($column['highTagIds'])) {
            if (is_array($column['highTagIds']) and !empty($column['highTagIds'])) {
                $column['highTagIds'] = '|' . implode('|', $column['highTagIds']) . '|';
            } else {
                $column['highTagIds'] = '';
            }
        }
        return $column;
    }


 
    public static function unserializes(array $column)
    {
        if (empty($column)) {
            return $column;
        }

        $column['lowTagIds'] = empty($column['lowTagIds']) ? array() : explode('|', trim($column['lowTagIds'], '|'));
        $column['middleTagIds'] = empty($column['middleTagIds']) ? array() : explode('|', trim($column['middleTagIds'], '|'));
        $column['highTagIds'] = empty($column['highTagIds']) ? array() : explode('|', trim($column['highTagIds'], '|'));


        return $column;
    }
}

