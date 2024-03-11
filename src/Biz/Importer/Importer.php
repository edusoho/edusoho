<?php

namespace Biz\Importer;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\FileToolkit;
use Biz\Common\CommonException;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

abstract class Importer
{
    const DANGER_STATUS = 'danger';
    const ERROR_STATUS = 'error';
    const SUCCESS_STATUS = 'success';
    const MAX_IMPORTER_COMPLEXITY = 8; //单请求最大导入复杂度（例如：人数*单次课程|班级数量<8）

    protected $biz;
    protected $necessaryFields = ['nickname' => '用户名', 'verifiedMobile' => '手机', 'email' => '邮箱'];
    protected $objWorksheet;
    protected $rowTotal = 0;
    protected $colTotal = 0;
    protected $excelFields = [];
    protected $passValidateUser = [];
    protected $maxRowTotal = 1000;

    abstract public function import(Request $request);

    abstract public function getTemplate(Request $request);

    abstract public function tryImport(Request $request);

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function render($view, $params = [])
    {
        global $kernel;

        return $kernel->getContainer()->get('templating')->renderResponse($view, $params);
    }

    protected function createDangerResponse($message)
    {
        if (!is_string($message)) {
            throw CommonException::ERROR_PARAMETER();
        }

        return [
            'status' => self::DANGER_STATUS,
            'message' => $message,
        ];
    }

    protected function createErrorResponse(array $errorInfo)
    {
        return [
            'status' => self::ERROR_STATUS,
            'errorInfo' => $errorInfo,
        ];
    }

    protected function createSuccessResponse(array $importData, array $checkInfo, array $customParams = [])
    {
        $response = [
            'status' => self::SUCCESS_STATUS,
            'importData' => $importData,
            'checkInfo' => $checkInfo,
        ];
        $response = array_merge($customParams, $response);

        return $response;
    }

    protected function trim($data)
    {
        $data = trim($data);
        $data = str_replace(' ', '', $data);
        $data = str_replace('\n', '', $data);
        $data = str_replace('\r', '', $data);
        $data = str_replace('\t', '', $data);

        return $data;
    }

    protected function checkRepeatData()
    {
        $errorInfo = [];
        $checkFields = array_keys($this->necessaryFields);
        $fieldSort = $this->getFieldSort();

        foreach ($checkFields as $checkField) {
            $nicknameData = [];

            foreach ($fieldSort as $key => $value) {
                if ($value['fieldName'] == $checkField) {
                    $nickNameCol = $value['num'];
                }
            }

            for ($row = 3; $row <= $this->rowTotal; ++$row) {
                $nickNameColData = $this->objWorksheet->getCellByColumnAndRow($nickNameCol, $row)->getValue();

                $nicknameData[] = $nickNameColData.'';
            }

            $info = $this->arrayRepeat($nicknameData, $nickNameCol);

            empty($info) ? '' : $errorInfo[] = $info;
        }

        return $errorInfo;
    }

    protected function getFieldSort()
    {
        $fieldSort = [];
        $necessaryFields = $this->necessaryFields;
        $excelFields = $this->excelFields;

        foreach ($excelFields as $key => $value) {
            if (in_array($value, $necessaryFields)) {
                foreach ($necessaryFields as $fieldKey => $fieldValue) {
                    if ($value == $fieldValue) {
                        $fieldSort[$fieldKey] = ['num' => $key, 'fieldName' => $fieldKey];
                        break;
                    }
                }
            }
        }

        return $fieldSort;
    }

    protected function arrayRepeat($array, $nickNameCol)
    {
        $repeatArrayCount = array_count_values($array);
        $repeatRow = '';

        foreach ($repeatArrayCount as $key => $value) {
            if ($value > 1 && !empty($key)) {
                $repeatRow .= sprintf('第%s列重复，重复内容如下:', ($nickNameCol + 1)).'<br>';

                for ($i = 1; $i <= $value; ++$i) {
                    $row = array_search($key, $array) + 3;

                    $repeatRow .= sprintf('第%s行：%s', $row, $key).'<br>';

                    unset($array[$row - 3]);
                }
            }
        }

        return $repeatRow;
    }

    protected function getUserData()
    {
        $userCount = 0;
        $fieldSort = $this->getFieldSort();
        $validate = [];
        $allUserData = [];

        for ($row = 3; $row <= $this->rowTotal; ++$row) {
            for ($col = 0; $col < $this->colTotal; ++$col) {
                $infoData = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $columnsData[$col] = $infoData.'';
            }

            foreach ($fieldSort as $sort) {
                $userData[$sort['fieldName']] = trim($columnsData[$sort['num']]);
                $fieldCol[$sort['fieldName']] = $sort['num'] + 1;
            }

            $emptyData = array_count_values($userData);

            if (isset($emptyData['']) && count($userData) == $emptyData['']) {
                $checkInfo[] = sprintf('第%s行为空行，已跳过', $row);
                continue;
            }

            $info = $this->validExcelFieldValue($userData, $row, $fieldCol);
            empty($info) ? '' : $errorInfo[] = $info;

            $userCount = $userCount + 1;

            $allUserData[] = $userData;

            if (empty($errorInfo)) {
                if (!empty($userData['nickname'])) {
                    $user = $this->getUserService()->getUserByNickname($userData['nickname']);
                } elseif (!empty($userData['email'])) {
                    $user = $this->getUserService()->getUserByEmail($userData['email']);
                } elseif (!empty($userData['verifiedMobile'])) {
                    $user = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
                }

                if (!empty($user)) {
                    $this->filterUser($user);
                }

                $validate[] = array_merge($user, ['row' => $row]);
            }

            unset($userData);
        }

        $this->passValidateUser = $validate;

        $data['errorInfo'] = empty($errorInfo) ? [] : $errorInfo;
        $data['checkInfo'] = empty($checkInfo) ? [] : $checkInfo;
        $data['userCount'] = $userCount;
        $data['allUserData'] = empty($this->passValidateUser) ? [] : $this->passValidateUser;

        return $data;
    }

    protected function validateExcelFile($file)
    {
        if (!is_object($file)) {
            return $this->createDangerResponse('请选择上传的文件');
        }

        if (FileToolkit::validateFileExtension($file, 'xls xlsx')) {
            return $this->createDangerResponse('Excel格式不正确！');
        }

        $this->excelAnalyse($file);

        if ($this->rowTotal > $this->maxRowTotal) {
            return $this->createDangerResponse('Excel超过'.$this->maxRowTotal.'行数据!');
        }

        if (!$this->checkNecessaryFields($this->excelFields)) {
            return $this->createDangerResponse('缺少必要的字段');
        }
    }

    protected function filterUser(&$user)
    {
        unset($user['password']);
        unset($user['salt']);
        unset($user['payPassword']);
        unset($user['payPasswordSalt']);
        unset($user['createdTime']);
        unset($user['updatedTime']);
        unset($user['distributorToken']);
    }

    protected function excelAnalyse($file)
    {
        $objPHPExcel = \PHPExcel_IOFactory::load($file);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelFields = [];

        for ($col = 0; $col < $highestColumnIndex; ++$col) {
            $fieldTitle = $objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
            empty($fieldTitle) ? '' : $excelFields[$col] = $this->trim($fieldTitle);
        }

        $rowAndCol = ['rowLength' => $highestRow, 'colLength' => $highestColumnIndex];

        $this->objWorksheet = $objWorksheet;
        $this->rowTotal = $highestRow;
        $this->colTotal = $highestColumnIndex;
        $this->excelFields = $excelFields;

        return [$objWorksheet, $rowAndCol, $excelFields];
    }

    protected function checkNecessaryFields($excelFields)
    {
        return ArrayToolkit::some(
            $this->necessaryFields,
            function ($fields) use ($excelFields) {
                return in_array($fields, array_values($excelFields));
            }
        );
    }

    protected function checkPassedRepeatData()
    {
        $passedUsers = $this->passValidateUser;
        $ids = [];
        $repeatRow = [];
        $repeatIds = [];

        foreach ($passedUsers as $key => $passedUser) {
            if (in_array($passedUser['id'], $ids) && !in_array($passedUser['id'], $repeatIds)) {
                $repeatIds[] = $passedUser['id'];
            } else {
                $ids[] = $passedUser['id'];
            }
        }

        foreach ($passedUsers as $key => $passedUser) {
            if (in_array($passedUser['id'], $repeatIds)) {
                $repeatRow[$passedUser['id']][] = $passedUser['row'];
            }
        }

        $repeatRowInfo = '';
        $repeatArray = [];

        if (!empty($repeatRow)) {
            $repeatRowInfo .= '字段对应用户数据重复'.'</br>';

            foreach ($repeatRow as $row) {
                $repeatRowInfo .= '重复行：'.'</br>';

                foreach ($row as $value) {
                    $repeatRowInfo .= sprintf('第%s行', $value);
                }

                $repeatRowInfo .= '</br>';

                $repeatArray[] = $repeatRowInfo;
                $repeatRowInfo = '';
            }
        }

        return $repeatArray;
    }

    protected function validExcelFieldValue($userData, $row, $fieldCol)
    {
        $errorInfo = '';

        if (!empty($userData['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($userData['nickname']);
        } elseif (!empty($userData['email'])) {
            $user = $this->getUserService()->getUserByEmail($userData['email']);
        } elseif (!empty($userData['verifiedMobile'])) {
            $user = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
        }

        if (!empty($user) && !empty($userData['email']) && !in_array($userData['email'], $user)) {
            $user = null;
        }

        if (!empty($user) && !empty($userData['verifiedMobile']) && !in_array($userData['verifiedMobile'], $user)) {
            $user = null;
        }

        if (!empty($user) && !empty($userData['nickname']) && !in_array($userData['nickname'], $user)) {
            $user = null;
        }

        if (!$user) {
            $errorInfo = sprintf('第%s行的信息有误，用户数据不存在，请检查。', $row);
        }

        return $errorInfo;
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

        $importData = $this->getUserData();

        if (empty($importData['errorInfo'])) {
            $passedRepeatInfo = $this->checkPassedRepeatData();
            if ($passedRepeatInfo) {
                return $this->createErrorResponse($passedRepeatInfo);
            }
        } else {
            return $this->createErrorResponse($importData['errorInfo']);
        }

        return $this->createSuccessResponse(
            $importData['allUserData'],
            $importData['checkInfo'],
            array_merge($request->request->all(), ['chunkNum' => $this->calculateChunkNum()])
        );
    }

    protected function calculateChunkNum($singleComplexity = 1)
    {
        if (empty($singleComplexity)) {
            return self::MAX_IMPORTER_COMPLEXITY;
        }

        return ceil(self::MAX_IMPORTER_COMPLEXITY / $singleComplexity);
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
