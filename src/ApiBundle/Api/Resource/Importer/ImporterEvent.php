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
            return $this->check($type);
        }

        if (self::IMPORT_EVENT == $event) {
            return $this->import($type);
        }
    }

    protected function import($type)
    {
        $importer = $this->getImporterFactory($type);
        $importer->tryImport($this->container->get('request'));
        $importerResult = $importer->import($this->container->get('request'));

        return $importerResult;
    }

    protected function check($type)
    {
        $importer = $this->getImporterFactory($type);
        $importer->tryImport($this->container->get('request'));
        $checkResult = $importer->check($this->container->get('request'));
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