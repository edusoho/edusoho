<?php

namespace Topxia\Service\Theme\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class ThemeServiceTest extends BaseTestCase
{



	private function getThemeService()
    {
        return $this->getServiceKernel()->createService('Theme.ThemeService');
    }
}