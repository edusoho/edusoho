<?php

namespace ApiBundle\Api\Resource\Importer;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;

class ImporterEvent extends AbstractResource
{
    const CHECK_EVENT = 'check';

    const IMPORT_EVENT = 'import';

    public function add(ApiRequest $request, $event)
    {
        if (!in_array($event, [self::CHECK_EVENT, self::IMPORT_EVENT])) {
            throw CommonException::ERROR_PARAMETER();
        }

        $type = $request->request->get('type');
        if (empty($type)) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (self::CHECK_EVENT == $event) {
            return $this->check($request, $event);
        }

        if (self::IMPORT_EVENT == $event) {
            return $this->import($request, $event);
        }
    }

    protected function check($request, $type)
    {
        $importer = $this->getImporterFactory($type);
        $importer->tryImport($request);
        $importerResult = $importer->import($request);

        return $importerResult;
    }

    protected function import($request, $type)
    {
        $importer = $this->getImporterFactory($type);
        $importer->tryImport($request);
        $checkResult = $importer->check($request);
        if (!empty($checkResult['message'])) {
            $checkResult['message'] = $this->container->get('translator')->trans($checkResult['message']);
        }

        return $checkResult;
    }

    protected function getImporterFactory($importType)
    {
        $biz = $this->getBiz();
        if (!isset($biz["importer.{$importType}"])) {
            return null;
        }

        return $biz["importer.{$importType}"];
    }
}