<?php
namespace Topxia\Service\Common;

use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileService;

class BbCode extends BaseService
{
	public function analyzeBbCode ($bbCode)
	{
		$isFind = preg_match_all("#\[image\].*?\[\/image\]|\[video\].*?\[\/video\]#", $bbCode, $matches);

		$urls = array(
			'image' => array(),
			'video' => array()
		);
		foreach ($matches[0] as $value) {

			if ( preg_match_all("#\[[a-zA-Z]*\]#", $value, $urlMatchs) == 0) continue;

			if ($urlMatchs[0][0] == '[image]') {
				$urls['image'][] = str_replace(array('[image]', '[/image]'), '', $value);
			}

			if ($urlMatchs[0][0] == '[video]') {
				$urls['video'][] = str_replace(array('[video]', '[/video]'), '', $value);
			}
		}

		return $urls;
	}

	public static function htmlToBbCode (string $html)
	{
		//
	}

	public function makeBbCode ($url, $type)
	{
		$file = $this->getUploadFileService()->getFileByHashId($url);

		if (!in_array($type, array('image', 'video'))) {
			throw new Exception("The Type Is Not Aollowed", 1);
		}

		$method = 'get' . ucfirst($type);
		return $this->$method($file);
	}

	private function getImage ($file)
	{
		return '[image]'.$file['hashId'].'[/image]';
	}

	private function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }
}