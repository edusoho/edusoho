<?php

namespace Custom\WebBundle;

use Topxia\Common\ExtensionalBundle;

class CustomWebBundle extends ExtensionalBundle
{
	public function getEnabledExtensions()
    {
        return array('DataTag');
    }
}
