<?php
namespace Custom\Service\Taxonomy\Impl;
use Topxia\Service\Taxonomy\Impl\TagServiceImpl as BaseTagServiceImpl ;
use Custom\Service\Taxonomy\CustomTagService;
use Topxia\Common\ArrayToolkit;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class TagServiceImpl extends BaseTagServiceImpl implements CustomTagService
{

    public function addTag(array $tag)
    {
        $tag = ArrayToolkit::parts($tag, array('name','description','weight'));

        $this->filterTagFields($tag);
        $tag['createdTime'] = time();

        $tag = $this->getTagDao()->addTag($tag);

        $this->getLogService()->info('tag', 'create', "添加标签{$tag['name']}(#{$tag['id']})");

        return $tag;
    }

    public function updateTag($id, array $fields)
    {
        $tag = $this->getTag($id);
        if (empty($tag)) {
            throw $this->createServiceException("标签(#{$id})不存在，更新失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('name','description','weight'));
        $this->filterTagFields($fields, $tag);

        $this->getLogService()->info('tag', 'update', "编辑标签{$fields['name']}(#{$id})");

        return $this->getTagDao()->updateTag($id, $fields);
    }

    public function findAllTags($start, $limit){
        return $this->getCustomTagDao()->findAllTags($start, $limit);
    }

        private function filterTagFields(&$tag, $relatedTag = null)
    {
        if (empty($tag['name'])) {
            throw $this->createServiceException('标签名不能为空，添加失败！');
        }

        $tag['name'] = (string) $tag['name'];

        $exclude = $relatedTag ? $relatedTag['name'] : null;
        if (!$this->isTagNameAvalieable($tag['name'], $exclude)) {
            throw $this->createServiceException('该标签名已存在，添加失败！');
        }

        return $tag;
    }

    public function changeTagAvatar($tagId, $filePath, array $options)
    {
        $tag = $this->getTag($tagId);
        if (empty($tag)) {
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
            'smallAvatar' => $tag['smallAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $tag['smallAvatar']) : null,
            'mediumAvatar' => $tag['mediumAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $tag['mediumAvatar']) : null,
            'largeAvatar' => $tag['largeAvatar'] ? $this->getKernel()->getParameter('topxia.upload.public_directory') . '/' . str_replace('public://', '', $tag['largeAvatar']) : null
        );

        array_map(function($oldAvatar){
            if (!empty($oldAvatar)) {
                @unlink($oldAvatar);
            }
        }, $oldAvatars);

        return  $this->getTagDao()->updateTag($tagId, array(
            'smallAvatar' => $smallFileRecord['uri'],
            'mediumAvatar' => $mediumFileRecord['uri'],
            'largeAvatar' => $largeFileRecord['uri'],
        ));
    }


    private function getTagDao()
    {
        return $this->createDao('Taxonomy.TagDao');
    }
    private function getCustomTagDao()
    {
        return $this->createDao('Custom:Taxonomy.TagDao');
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