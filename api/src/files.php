<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use AppBundle\Util\UploadToken;

$api = $app['controllers_factory'];

return $api;