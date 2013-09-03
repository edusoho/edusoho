<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends BaseCommand
{

	protected function configure()
	{
		$this->setName ( 'topxia:init' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// $output->writeln('文件设置初始化');

		// $fileSettings = array(
		// 	'public_directory' => 'web/files',
		// 	'public_web_path' => '/files',
		// 	'private_directory' => 'private_files',
		// );
		// $this->getSettingService()->set('file', $fileSettings);

		$output->writeln('开始初始化系统');

		$this->initCategory($output);
		$this->initTag($output);
		$this->initFile($output);

		$output->writeln('初始化系统完毕');
	}

	private function initTag($output)
	{
		$output->write('  初始化标签');

		$this->getTagService()->addTag(array('name' => '默认标签'));

		$output->writeln('...成功');
	}

	private function initCategory($output)
	{
		$output->write('  初始化分类分组');

		$categories = $this->getCategoryService()->findAllCategories();
		foreach ($categories as $category) {
			$this->getCategoryService()->deleteCategory($category['id']);
		}

		$groups = $this->getCategoryService()->findAllGroups();
		foreach ($groups as $group) {
			$this->getCategoryService()->deleteGroup($group['id']);
		}

		$group = $this->getCategoryService()->addGroup(array(
			'name' => '课程分类',
			'code' => 'course',
			'depth' => 2,
		));

		$this->getCategoryService()->createCategory(array(
			'name' => '默认分类',
			'code' => 'default',
			'weight' => 100,
			'groupId' => $group['id'],
			'parentId' => 0,
		));

		$output->writeln('...成功');
	}

	private function initFile($output)
	{
		$output->write('  初始化文件分组');

		$groups = $this->getFileService()->getAllFileGroups();
		foreach ($groups as $group) {
			$this->getFileService()->deleteFileGroup($group['id']);
		}

		$this->getFileService()->addFileGroup(array(
			'name' => '默认文件组',
			'code' => 'default',
			'public' => 1,
		));

		$this->getFileService()->addFileGroup(array(
			'name' => '缩略图',
			'code' => 'thumb',
			'public' => 1,
		));

		$this->getFileService()->addFileGroup(array(
			'name' => '课程',
			'code' => 'course',
			'public' => 1,
		));

		$this->getFileService()->addFileGroup(array(
			'name' => '用户',
			'code' => 'user',
			'public' => 1,
		));

		$this->getFileService()->addFileGroup(array(
			'name' => '课程私有文件',
			'code' => 'course_private',
			'public' => 0,
		));

		$output->writeln('...成功');
	}

	protected function getSettingService()
	{
		return $this->getServiceKernel()->createService('System.SettingService');
	}

	protected function getCategoryService()
	{
		return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
	}

	protected function getTagService()
	{
		return $this->getServiceKernel()->createService('Taxonomy.TagService');
	}

	protected function getFileService()
	{
		return $this->getServiceKernel()->createService('Content.FileService');
	}

}