<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Topxia\WebBundle\Util\UploadToken;

$api = $app['controllers_factory'];

return $api;