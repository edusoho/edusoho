<?php
namespace Topxia\Service\Content\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Content\FileService;
use Symfony\Component\HttpFoundation\File\File;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;

class FileServiceImpl extends BaseService implements FileService
{

	/**
	 * {@inheritdoc}
	 */
	public function getFiles($group, $start, $limit)
	{
		if (empty($group)) {
			return $this->getFileDao()->findFiles($start, $limit);
		}

		$group = $this->getGroupDao()->findGroupByCode($group);
		if (empty($group)) {
			return array();
		}

		return $this->getFileDao()->findFilesByGroupId($group['id'], $start, $limit);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFileCount($group = null)
	{
		if (empty($group)) {
			return $this->getFileDao->findFileCount();
		}

		$group = $this->getGroupDao()->findGroupByCode($group);
		if (empty($group)) {
			return 0;
		}

		return $this->getFileDao()->findFileCountByGroupId($group['id']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTargetFiles($target)
	{

	}

	public function uploadFile($group, File $file, $target = null)
	{
		$group = $this->getGroupDao()->findGroupByCode($group);
		$user = $this->getCurrentUser();
		$record = array();
		$record['userId'] = $user['id'];
		$record['groupId'] = $group['id'];
		$record['mime'] = $file->getMimeType();
		$record['size'] = $file->getSize();
		$record['uri'] = $this->generateUri($group, $file);
		$record['createdTime'] = time();
		$record = $this->getFileDao()->addFile($record);
		$record['file'] = $this->saveFile($file, $record['uri']);
		return $record;
	}

	public function deleteFile($id)
	{
		$this->getFileDao()->deleteFile($id);
	}

	public function bindFile($id, $target)
	{

	}

	public function bindFiles(Array $ids, $target)
	{

	}

	public function unbindFile($id, $target)
	{

	}

	public function unbindFiles(Array $ids, $target)
	{

	}

	public function unbindTargetFiles($target)
	{

	}

	private function saveFile($file, $uri)
	{
		$parsed = $this->parseFileUri($uri);

		$setting = $this->getSettingService()->get("file");
		if (empty($setting[$parsed['access'].'_directory'])) {
			throw $this->createServiceException('文件上传失败，公开文件存储路径尚未设置。');
		}

		if ($parsed['access'] == 'public') {
			$directory = $this->getKernel()->getRootPath() . '/web/' . $setting[$parsed['access'].'_directory'];
		} else {
			$directory = $this->getKernel()->getRootPath() . '/' . $setting[$parsed['access'].'_directory'];
		}

		if (!is_writable($directory)) {
			throw $this->createServiceException("文件上传路径{$directory}不可写，文件上传失败。");
		}
		$directory .= '/' . $parsed['directory'];

		return $file->move($directory, $parsed['name']);
	}

    private function generateUri ($group, $file)
    {
    	$uri = ($group['public'] ? 'public://' : 'private://') . $group['code'] . '/';
        $uri .= date('Y') . '/' . date('m-d') . '/' . date('His');
        $uri .= substr(uniqid(), - 6) . substr(uniqid('', true), - 6);
        $ext = $file->guessExtension();
        $ext = $ext == 'jpeg' ? 'jpg' : $ext;
        $uri .= '.' . $ext;
        return $uri;
    }

	/**
	 * {@inheritdoc}
	 */
    public function parseFileUri($uri)
    {
    	$parsed = array();
    	$parts = explode('://', $uri);
    	if (empty($parts) or count($parts)!=2) {
    		throw $this->createServiceException('解析文件URI({$uri})失败！');
    	}
    	$parsed['access'] = $parts[0];
    	$parsed['path'] = $parts[1];
    	$parsed['directory'] = dirname($parsed['path']);
    	$parsed['name'] = basename($parsed['path']);
    	return $parsed;
    }

    public function getFileGroup($id)
    {
    	return $this->getGroupDao()->getGroup($id);
    }

    public function getFileGroupByCode($code)
    {
    	return $this->getGroupDao()->findGroupByCode($code);
    }

    public function getAllFileGroups()
    {
    	return $this->getGroupDao()->findAllGroups();
    }

    public function thumbnailFile(array $file, array $options)
    {
    	$options = array('quality' => 90, 'mode' => 'outbound') + $options;

		$imagine = new Imagine();

		$size    = new \Imagine\Image\Box($options['width'], $options['height']);

		$uri = $this->parseFileUri($file['uri']);

		$savePath = tempnam(sys_get_temp_dir(), '_thumb_');
		unlink($savePath);

		// var_dump($options, $file['file']->getRealPath());exit();

        $imagine->open($file['file']->getRealPath())
        	->thumbnail($size, $options['mode'])
        	->save($savePath . '.jpg' , array('quality' => $options['quality']));

        $file = new File($savePath. '.jpg');

        $file = $this->uploadFile('thumb', $file);

        return $file;
	}

    private function getSettingService()
    {
    	return $this->createService('System.SettingService');
    }

    private function getFileDao()
    {
        return $this->createDao('Content.FileDao');
    }

	private function getGroupDao()
	{
        return $this->createDao('Content.FileGroupDao');
	}

}