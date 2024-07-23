<?php

namespace Biz\Importer;

use Biz\Sensitive\Service\SensitiveService;
use Biz\User\CurrentUser;
use Biz\User\UserException;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class SensitiveImporter extends Importer
{
    const MAX_SENSITIVE_IMPORTER_COMPLEXITY = 400; //单请求最大导入复杂度

    protected $type = 'sensitive';
    protected $necessaryFields = ['name' => '敏感词', 'state' => '类型'];
    protected $keywordStates = ['banned' => '禁用', 'replaced' => '屏蔽'];
    protected $checkFields = ['name'];
    protected $maxRowTotal = 5000;

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $totalCount = count($importData);
        if (empty($totalCount)) {
            return ['successCount' => $totalCount];
        }

        try {
            $this->biz['db']->beginTransaction();

            foreach ($importData as $keyword) {
                $existedKeyword = $this->getSensitiveService()->getKeywordByName($keyword['name']);

                if (empty($existedKeyword)) {
                    $this->getSensitiveService()->addKeyword($keyword['name'], $keyword['state']);
                } else {
                    $this->getSensitiveService()->updateKeyword($existedKeyword['id'], $keyword);
                }
            }

            $this->biz['db']->commit();
        } catch (Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return ['successCount' => $totalCount];
    }

    public function getTemplate(Request $request)
    {
        return $this->render('admin-v2/system/user-content-control/sensitive/importer.html.twig', [
            'importerType' => $this->type,
        ]);
    }

    public function tryImport(Request $request)
    {
        if (!$this->getCurrentUser()->hasPermission('admin_v2_system_sensitive_words')) {
            throw UserException::PERMISSION_DENIED();
        }
    }

    public function check(Request $request)
    {
        $file = $request->files->get('excel');
        $danger = $this->validateExcelFile($file);
        if (!empty($danger)) {
            return $danger;
        }

        $repeatInfo = $this->checkRepeatData();
        if (!empty($repeatInfo)) {
            return $this->createErrorResponse($repeatInfo);
        }

        $importData = $this->getImportData();

        if (!empty($importData['errorInfo'])) {
            return $this->createErrorResponse($importData['errorInfo']);
        }

        return $this->createSuccessResponse(
            $importData['data'],
            $importData['checkInfo'],
            array_merge($request->request->all(), ['chunkNum' => $this->calculateChunkNum()])
        );
    }

    protected function checkRepeatData()
    {
        $errorInfo = [];
        $fieldSort = $this->getFieldSort();

        foreach ($this->checkFields as $checkField) {
            $checkFieldData = [];
            foreach ($fieldSort as $key => $value) {
                if ($value['fieldName'] === $checkField) {
                    $checkFieldCol = $value['num'];
                }
            }

            for ($row = 3; $row <= $this->rowTotal; ++$row) {
                $checkFieldColData = $this->objWorksheet->getCellByColumnAndRow($checkFieldCol, $row)->getValue();

                $checkFieldData[] = $checkFieldColData.'';
            }

            $info = $this->arrayRepeat($checkFieldData, $checkFieldCol);

            empty($info) ? '' : $errorInfo[] = $info;
        }

        return $errorInfo;
    }

    protected function getImportData()
    {
        $totalCount = 0;
        $fieldSort = $this->getFieldSort();
        $validate = [];
        $data = [];

        for ($row = 3; $row <= $this->rowTotal; ++$row) {
            for ($col = 0; $col < $this->colTotal; ++$col) {
                $infoData = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData.'';
            }

            foreach ($fieldSort as $sort) {
                $keyword[$sort['fieldName']] = trim($columnsData[$sort['num']]);
                $fieldCol[$sort['fieldName']] = $sort['num'] + 1;
            }

            $emptyData = array_count_values($keyword);
            if (isset($emptyData['']) && count($keyword) == $emptyData['']) {
                $checkInfo[] = sprintf('第%s行为空行，已跳过', $row);
                continue;
            }

            $info = $this->validExcelFieldValue($keyword, $row, $fieldCol);
            empty($info) ? '' : $errorInfo[] = $info;

            ++$totalCount;
            $keywordData[] = [
                'name' => $keyword['name'],
                'state' => $this->transKeywordState($keyword['state']),
            ];

            unset($keyword);
        }

        $data['errorInfo'] = empty($errorInfo) ? [] : $errorInfo;
        $data['checkInfo'] = empty($checkInfo) ? [] : $checkInfo;
        $data['totalCount'] = $totalCount;
        $data['data'] = $keywordData;

        return $data;
    }

    protected function transKeywordState($state)
    {
        foreach ($this->keywordStates as $key => $value) {
            if ($state === $value) {
                return $key;
            }
        }
    }

    protected function validExcelFieldValue($keywordData, $row, $fieldCol)
    {
        $errorInfo = '';

        if (empty($keywordData['name']) || empty($keywordData['state'])) {
            $errorInfo = sprintf('第%s行的敏感词信息缺失，请检查。', $row);
        }

        if (!in_array($keywordData['state'], array_values($this->keywordStates))) {
            $errorInfo = sprintf('第%s行的敏感词类型不正确，请检查。', $row);
        }

        return $errorInfo;
    }

    protected function calculateChunkNum($singleComplexity = 1)
    {
        if (empty($singleComplexity)) {
            return self::MAX_SENSITIVE_IMPORTER_COMPLEXITY;
        }

        return ceil(self::MAX_SENSITIVE_IMPORTER_COMPLEXITY / $singleComplexity);
    }

    /**
     * @return CurrentUser
     */
    protected function getCurrentUser()
    {
        $biz = $this->biz;

        return $biz['user'];
    }

    /**
     * @return SensitiveService
     */
    protected function getSensitiveService()
    {
        return $this->biz->service('Sensitive:SensitiveService');
    }
}
