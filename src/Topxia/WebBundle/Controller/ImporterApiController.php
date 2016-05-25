<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\FileToolkit;
use Topxia\Common\SimpleValidator;
use Topxia\Service\Checker\CheckerFactory;
use Topxia\Service\Importer\ImporterFactory;

class ImporterApiController extends BaseController
{
    public function checkAction(Request $request, $type)
    {
        $checker = CheckerFactory::create($type);
        $checkResult = $checker->check($request);
        return $this->createJsonResponse($checkResult);
    }

    public function importAction(Request $request)
    {
        $importerType = $request->request->get('type');
        $importer = ImporterFactory::create($importerType);
        return $this->createJsonResponse($importer->import($request->request->all()));
    }
}
