<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Importer\CheckerFactory;
use Topxia\Service\Importer\ImporterFactory;

class ImporterApiController extends BaseController
{
    public function checkAction(Request $request, $type)
    {
        $importer = ImporterFactory::create($type);
        $checkResult = $importer->check($request);
        return $this->createJsonResponse($checkResult);
    }

    public function importAction(Request $request, $type)
    {
        $importer = ImporterFactory::create($type);
        $importerResult = $importer->import($request->request->all());
        return $this->createJsonResponse($importerResult);
    }

    public function templateAction(Request $request, $type)
    {
        $params = $request->query->all();
        $importer = ImporterFactory::create($type);
        $template = $importer->getTemplate();
        return $this->render($template, $params);
    }
}
