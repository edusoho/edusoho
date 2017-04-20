<?php

namespace Topxia\MobileBundleV2\Processor;

interface TestpaperProcessor
{
    public function doTestpaper();

    public function showTestpaper();

    public function reDoTestpaper();

    public function getTestpaperResult();

    public function finishTestpaper();

    public function uploadQuestionImage();

    public function myTestpaper();

    public function favoriteQuestion();
}
