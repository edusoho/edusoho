<?php
namespace Topxia\MobileBundleV2\Service;

interface TestpaperService
{
	public function doTestpaper();

	public function reDoTestpaper();

	public function getTestpaperResult();

	public function finishTestpaper(); 

	public function uploadQuestionImage();

	public function myTestpaper();

	public function favoriteQuestion();
}