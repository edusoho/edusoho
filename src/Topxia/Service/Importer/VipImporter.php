<?php


namespace Topxia\Service\Importer;


use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\FileToolkit;
use Topxia\Common\SimpleValidator;

class VipImporter extends Importer
{
    protected $type = 'vip';

    public function import(Request $request)
    {
        $userDataArray = $request->request->get('importData');
        foreach ($userDataArray as $userData) {
            $user = $this->getUserData($userData);

            $viplevel = $this->getLevelService()->getLevelByName($userData['viplevelName']);

            $order = $this->getOrderService()->createOrder(array(
                'userId'     => $user['id'],
                'title'      => $this->getKernel()->trans('批量导入会员'),
                'targetType' => 'vip',
                'targetId'   => $viplevel['id'],
                'amount'     => '0',
                'totalPrice' => '0',
                'payment'    => 'none',
                'snPrefix'   => 'V'
            ));

            $this->getOrderService()->payOrder(array(
                'sn'       => $order['sn'],
                'status'   => 'success',
                'amount'   => $order['amount'],
                'paidTime' => time()
            ));

            if (isset($userData['upgradeWay']) && $userData['upgradeWay'] == 'renew') {
                $this->getVipService()->renewMember($user['id'], $userData['viplevelTime'], 'month', $order['id']);
            } elseif (isset($userData['upgradeWay']) && $userData['upgradeWay'] == 'upgrade') {
                $this->getVipService()->upgradeMember($user['id'], $viplevel['id']);
                $this->getVipService()->renewMember($user['id'], $userData['viplevelTime'], 'month', $order['id']);
            } else {
                $this->getVipService()->becomeMember($user['id'], $viplevel['id'], $userData['viplevelTime'], 'month', $orderId = 0); //升级会员加入订单
            }

            $message = $this->getKernel()->trans('您已被管理员添加为%viplevelName%会员', array('%viplevelName%' => $userData['viplevelName']));
            $this->getNotificationService()->notify($user['id'], 'default', $message);
        }

        return "finished";
    }

    public function check(Request $request)
    {
        $allUserData = array();
        $userCount   = 0;
        $errorInfo   = array();
        $checkInfo   = array();
        $file        = $request->files->get('excel');

        if (!is_object($file)) {
            return $this->createDangerResponse($this->getKernel()->trans('请选择上传的文件'));
        }

        if (FileToolkit::validateFileExtension($file, 'xls xlsx')) {
            return $this->createDangerResponse($this->getKernel()->trans('Excel格式不正确！'));
        }

        $objPHPExcel  = \PHPExcel_IOFactory::load($file);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow   = $objWorksheet->getHighestRow();

        $highestColumn      = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

        if ($highestRow > 1000) {
            return $this->createDangerResponse($this->getKernel()->trans('Excel超过1000行数据!'));
        }

        //预设字段
        $fieldArray = $this->getFieldArray();

        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $fieldTitle = $objWorksheet->getCellByColumnAndRow($col, 2)->getValue();
            $strs[$col] = $fieldTitle."";
        }

        //excel字段名
        $excelField = $strs;

        if (!$this->checkNecessaryFields($excelField)) {
            return $this->createDangerResponse($this->getKernel()->trans('缺少必要字段'));
        }

        $fieldSort = $this->getFieldSort($excelField, $fieldArray); //字段实际字符

        unset($fieldArray, $excelField);

        $repeatInfo = $this->checkRepeatData($row = 3, $fieldSort, $highestRow, $objWorksheet);

        for ($row = 3; $row <= $highestRow; $row++) {
            $strs = array();

            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $infoData   = $objWorksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
                $strs[$col] = $infoData."";
                unset($infoData);
            }

            foreach ($fieldSort as $sort) {
                $num = $sort['num'];
                $key = $sort['fieldName'];

                $userData[$key] = $strs[$num];
                $fieldCol[$key] = $num + 1;
            }

            unset($strs);
            //字段校验
            $emptyData = array_count_values($userData);

            if (isset($emptyData[""]) && count($userData) == $emptyData[""]) {
                $checkInfo[] = "第".$row."行为空行，已跳过";
                continue;
            }

            $errorInfo = array_merge($errorInfo, $repeatInfo);

            if ($this->validFields($userData, $row, $fieldCol)) {
                $errorInfo = array_merge($errorInfo, array_merge($repeatInfo, $this->validFields($userData, $row, $fieldCol)));
            }

            $tempUser = $this->getUserData($userData);

            if (!$tempUser) {
                $checkInfo[] = "第".$row."行的用户不存在，已跳过";
                continue;
            }

            if ($userData['viplevelTime'] == 0) {
                $checkInfo[] = "第".$row."行的用户会员时效不能为0，已跳过";
                continue;
            }

            if (empty($errorInfo)) {
                if (!empty($userData['nickname'])) {
                    $user = $this->getUserService()->getUserByNickname($userData['nickname']);
                } elseif (!empty($userData['email'])) {
                    $user = $this->getUserService()->getUserByEmail($userData['email']);
                } elseif (!empty($userData['verifiedMobile'])) {
                    $user = $this->getUserService()->getUserByVerifiedMobile($userData['verifiedMobile']);
                }

                $validate[] = array_merge($user, array('row' => $row));
            }

            $member = $this->getVipService()->getMemberByUserId($tempUser['id']);

            if (!empty($member)) {
                if ($member['deadline'] > time()) {
                    $checkInfo[] = "第".$row."行的用户已是会员，已跳过";
                    continue;
                } else {
                    $userData['upgradeWay'] = $this->checkMemberCanRenewOrUpgradeOrBecome($member, $userData['viplevelName']);
                }
            }

            $userCount = $userCount + 1;

            $allUserData[] = $userData;

            unset($userData);
        }

        if (empty($errorInfo) && !empty($validate)) {
            $errorInfo = $this->checkPassedRepeatData($validate);
        }

        if(!empty($errorInfo)){
            return $this->createErrorResponse($errorInfo);
        }

        return $this->createSuccessResponse($allUserData, $checkInfo);
    }

    public function getTemplate(Request $request)
    {
        return $this->render('VipBundle:VipAdmin:import.html.twig', array(
            'importerType' => $this->type
        ));
    }

    public function tryImport(Request $request)
    {
        $user = $this->getServiceKernel()->getCurrentUser();
        if(!$user->isAdmin()){
            throw new AccessDeniedException($this->getKernel()->trans('当前用户没有导入会员权限'));
        }
    }

    private function getFieldArray()
    {
        $userFieldArray = array();

        $fieldArray = array(
            "nickname"       => '用户名',
            "email"          => '邮箱',
            "verifiedMobile" => '手机',
            "viplevelName"   => '会员名称',
            "viplevelTime"   => '会员时效(整数)月'
        );

        $fieldArray = array_merge($fieldArray, $userFieldArray);
        return $fieldArray;
    }

    private function checkNecessaryFields($data)
    {
        $data = implode("", $data);
        $data = $this->trim($data);

        $nickname_array = explode("用户名", $data);

        if (count($nickname_array) <= 1) {
            return false;
        }

        $email_array = explode("邮箱", $data);

        if (count($email_array) <= 1) {
            return false;
        }

        $verifiedMobile_array = explode("手机", $data);

        if (count($verifiedMobile_array) <= 1) {
            return false;
        }

        $viplevelName_array = explode("会员名称", $data);

        if (count($viplevelName_array) <= 1) {
            return false;
        }

        $viplevelTime_array = explode("会员时效(整数)月", $data);

        if (count($viplevelTime_array) <= 1) {
            return false;
        }

        return true;
    }

    private function getFieldSort($excelField, $fieldArray)
    {
        $fieldSort = array();

        foreach ($excelField as $key => $value) {
            $value = $this->trim($value);

            if (in_array($value, $fieldArray)) {
                foreach ($fieldArray as $fieldKey => $fieldValue) {
                    if ($value == $fieldValue) {
                        $fieldSort[] = array("num" => $key, "fieldName" => $fieldKey);
                        break;
                    }
                }
            }
        }

        return $fieldSort;
    }

    private function checkRepeatData($row, $fieldSort, $highestRow, $objWorksheet)
    {
        $errorInfo   = array();
        $checkFields = array(
            'nickname',
            'verifiedMobile',
            'email'
        );

        foreach ($checkFields as $checkField) {
            $nicknameData = array();

            foreach ($fieldSort as $key => $value) {
                if ($value['fieldName'] == $checkField) {
                    $nickNameCol = $value['num'];
                }
            }

            for ($row = 3; $row <= $highestRow; $row++) {
                $nickNameColData = $objWorksheet->getCellByColumnAndRow($nickNameCol, $row)->getValue();

                $nicknameData[] = $nickNameColData."";
            }

            $info = $this->arrayRepeat($nicknameData, $nickNameCol);

            empty($info) ? '' : $errorInfo[] = $info;
        }

        return $errorInfo;
    }

    private function validFields($userData, $row, $fieldCol)
    {
        $errorInfo   = array();
        $targetLevel = $this->getLevelService()->getLevelByName($userData['viplevelName']);
        $tempUser    = $this->getUserData($userData);
        $member      = $this->getVipService()->getMemberByUserId($tempUser['id']);
        $tempLevel   = $this->getLevelService()->getLevel($member['levelId']);

        if (!empty($userData['nickname']) && !SimpleValidator::nickname($userData["nickname"])) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["nickname"]." 列 的数据存在问题，请检查。";
        }

        if (!empty($userData['email']) && !SimpleValidator::email($userData["email"])) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["email"]." 列 的数据存在问题，请检查。";
        }

        if (!empty($userData['verifiedMobile']) && !SimpleValidator::mobile($userData["verifiedMobile"])) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["verifiedMobile"]." 列 的数据存在问题，请检查。";
        }

        if (!SimpleValidator::numbers($userData['viplevelTime'])) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["viplevelTime"]." 列 的数据存在问题，请检查。";
        }

        if (empty($targetLevel) || (empty($targetLevel['enabled']))) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["viplevelName"]." 列 的会员等级不存在或已关闭，请检查。";
        }

        if ($tempLevel['seq'] > $targetLevel['seq']) {
            $errorInfo[] = "第 ".$row."行".$fieldCol["viplevelName"]." 列 的会员等级低于用户现有等级，请检查。";
        }

        return $errorInfo;
    }

    private function getUserData($userData)
    {
        $user = null;

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

        return $user;
    }

    private function arrayRepeat($array, $nickNameCol)
    {
        $repeatArray = array();

        $repeatArrayCount = array_count_values($array);

        $repeatRow = "";

        foreach ($repeatArrayCount as $key => $value) {
            if ($value > 1 && !empty($key)) {
                $repeatRow .= '第'.($nickNameCol + 1)."列重复:<br>";

                for ($i = 1; $i <= $value; $i++) {
                    $row = array_search($key, $array) + 3;

                    $repeatRow .= "第".$row."行"."    ".$key."<br>";

                    unset($array[$row - 3]);
                }
            }
        }

        return $repeatRow;
    }

    private function checkMemberCanRenewOrUpgradeOrBecome($member, $viplevelName)
    {
        $targetLevel = $this->getLevelService()->getLevelByName($viplevelName);
        $tempLevel   = $this->getLevelService()->getLevel($member['levelId']);

        if ($targetLevel['seq'] > $tempLevel['seq']) {
            return 'upgrade';
        }

        if ($targetLevel['seq'] = $tempLevel['seq']) {
            return 'renew';
        }
    }

    private function checkPassedRepeatData($passedUsers)
    {
        $ids  = array();
        $rows = array();

        $repeatRow = array();
        $repeatIds = array();

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
        $repeatArray   = array();

        if (!empty($repeatRow)) {
            $repeatRowInfo .= "字段对应用户数据重复</br>";

            foreach ($repeatRow as $row) {
                $repeatRowInfo .= "重复行：</br>";

                foreach ($row as $value) {
                    $repeatRowInfo .= "第".$value."行 ";
                }

                $repeatRowInfo .= "</br>";

                $repeatArray[] = $repeatRowInfo;
                $repeatRowInfo = '';
            }
        }

        return $repeatArray;
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getMessageService()
    {
        return $this->getServiceKernel()->createService('User.MessageService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}