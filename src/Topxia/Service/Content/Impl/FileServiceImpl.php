<?php
namespace Topxia\Service\Content\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Content\FileService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Topxia\Common\FileToolkit;

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
		$errors = FileToolkit::validateFileExtension($file);
		if ($errors) {
			@unlink($file->getRealPath());
			throw $this->createServiceException("该文件格式，不允许上传。");
		}

		$group = $this->getGroupDao()->findGroupByCode($group);
		$user = $this->getCurrentUser();
		$record = array();
		$record['userId'] = $user['id'];
		$record['groupId'] = $group['id'];
		// @todo fix it.
		$record['mime'] = '';
		// $record['mime'] = $file->getMimeType();
		$record['size'] = $file->getSize();
		$record['uri'] = $this->generateUri($group, $file);
		$record['createdTime'] = time();
		$record = $this->getFileDao()->addFile($record);
		$record['file'] = $this->saveFile($file, $record['uri']);

		return $record;
	}

	protected function validateFileExtension(File $file, $extensions)
	{
		if ($file instanceof UploadedFile) {
			$filename = $file->getClientOriginalName();
		} else {
			$filename = $file->getFilename();
		}
		$errors = array();
		$regex = '/\.(' . preg_replace('/ +/', '|', preg_quote($extensions)) . ')$/i';
		if (!preg_match($regex, $filename)) {
			$errors[] = "只允许上传以下扩展名的文件：" . $extensions;
		}
		return $errors;
	}

	protected function validateFileIsImage(File $file)
	{
		$errors = array();

		$info = image_get_info($file->getFileUri());
		if (!$info || empty($info['extension'])) {
			$errors[] = "只运行上传JPG、PNG、GIF格式的图片文件。";
		}

		return $errors;
	}

	protected function validateFileNameLength(File $file)
	{
		$errors = array();

		if (!$file->getFilename()) {
			$errors[] = "文件名为空，请给文件取个名吧。";
		}
		if (strlen($file->getFilename()) > 240) {
			$errors[] = "文件名超出了240个字符的限制，请重命名后再试。";
		}
		return $errors;
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
    
    public function getFileObject($fileId)
    {
        $fileInDao = $this->getFileDao()->getFile($fileId);
        $parsed = $this->parseFileUri($fileInDao['uri']);
        return new File($parsed['fullpath']);
    }

	private function saveFile($file, $uri)
	{
		$parsed = $this->parseFileUri($uri);
		if ($parsed['access'] == 'public') {
			$directory = $this->getKernel()->getParameter('topxia.upload.public_directory');
		} else {
			$directory = $this->getKernel()->getParameter('topxia.upload.private_directory');
		}

		if (!is_writable($directory)) {
			throw $this->createServiceException("文件上传路径{$directory}不可写，文件上传失败。");
		}
		$directory .= '/' . $parsed['directory'];

		return $file->move($directory, $parsed['name']);
	}

    private function generateUri ($group, $file)
    {
		if ($file instanceof UploadedFile) {
			$filename = $file->getClientOriginalName();
		} else {
			$filename = $file->getFilename();
		}

	    $filenameParts = explode('.', $filename);
	    $ext = array_pop($filenameParts);
	    if (empty($ext)) {
	    	throw $this->createServiceException('获取文件扩展名失败！');
	    }

    	$uri = ($group['public'] ? 'public://' : 'private://') . $group['code'] . '/';
        $uri .= date('Y') . '/' . date('m-d') . '/' . date('His');
        $uri .= substr(uniqid(), - 6) . substr(uniqid('', true), - 6);
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

    	$directory = $this->getKernel()->getParameter('topxia.upload.public_directory');

		if ($parsed['access'] == 'public') {
			$directory = $this->getKernel()->getParameter('topxia.upload.public_directory');
		} else {
			$directory = $this->getKernel()->getParameter('topxia.upload.private_directory');
		}
		$parsed['fullpath'] = $directory . '/' . $parsed['path'];

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

    public function addFileGroup($group)
    {
    	return $this->getGroupDao()->addGroup($group);
    }

    public function deleteFileGroup($id)
    {
    	return $this->getGroupDao()->deleteGroup($id);
    }

    public function thumbnailFile(array $file, array $options)
    {
    	$options = array('quality' => 90, 'mode' => 'outbound') + $options;

		$imagine = new Imagine();

		$size    = new \Imagine\Image\Box($options['width'], $options['height']);

		$uri = $this->parseFileUri($file['uri']);

		$savePath = tempnam(sys_get_temp_dir(), '_thumb_');
		unlink($savePath);

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

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
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