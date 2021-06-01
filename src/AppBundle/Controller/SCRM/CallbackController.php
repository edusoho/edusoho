<?php

namespace AppBundle\Controller\SCRM;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Goods\Service\GoodsService;
use Biz\ItemBankExercise\OperateReason;
use Biz\User\Dao\UserDao;
use ESCloud\SDK\Service\ScrmService;
use Symfony\Component\HttpFoundation\Request;

class CallbackController extends BaseController
{
    public function goodsAction(Request $request)
    {
        $query = $request->query->all();
        $this->filterQuery($query);

        $userInfo = $this->getScrmSdk()->getUserByToken($query['user_token']);

        $existUser = $this->getUserService()->getUserByScrmUuid($userInfo['unionId']);
        if (empty($existUser)) {
            try {
                $existUser = $this->registerUser($userInfo);
            } catch (\Exception $e) {
                return $this->createJsonResponse(['message' => $e->getMessage(), 'trans' => $e->getTraceAsString()]);
            }
        }
        try {
            $adminUser = $this->getUserService()->getUserByType('system');
            $this->authenticateUser($adminUser);
            $orderInfo = $this->getScrmSdk()->verifyOrder($query['order_id'], $query['receipt_token']);
            $specs = $this->getGoodsService()->getGoodsSpecs($orderInfo['specsId']);
            $courseMember = $this->getCourseMemberService()->getCourseMember($specs['targetId'], $existUser['id']);
            if (empty($courseMember)) {
                $data = [
                    'price' => $orderInfo['payAmount'],
                    'remark' => '通过SCRM添加',
                    'source' => 'outside',
                    'reason' => OperateReason::JOIN_BY_IMPORT,
                    'reasonType' => OperateReason::JOIN_BY_IMPORT_TYPE,
                ];
                $this->getCourseMemberService()->becomeStudent($specs['targetId'], $existUser['id'], $data);
            }
        } catch (\Exception $e) {
            $this->authenticateUser([
                'id' => 0,
                'nickname' => '游客',
                'currentIp' => '',
                'roles' => [],
            ]);

            return $this->createJsonResponse(['message' => $e->getMessage()]);
        }

        $this->getScrmSdk()->callbackTrading(['orderId' => $query['order_id'], 'status' => 'success']);
        $this->authenticateUser($existUser);

        $param = ['id' => $specs['targetId']];
        if (2 == $this->setting('wap.version') && DeviceToolkit::isMobileClient()) {
            $token = $this->getUserService()->makeToken('mobile_login', $existUser['id'], time() + 3600 * 24 * 30, []);
            $param['loginToken'] = $token;
        }

        return $this->redirect($this->generateUrl('my_course_show', $param));
    }

    protected function registerUser($userInfo)
    {
        $mobileUser = $this->getUserService()->getUserByVerifiedMobile($userInfo['phone']);
        if (!empty($mobileUser)) {
            return $mobileUser;
        }
        $registration = [];
        $registration['nickname'] = 'scrm_'.$this->getRandomString(5);
        $registration['email'] = 'scrm_'.$this->getRandomString(5).'@edusoho.net';
        $registration['password'] = '';
        $registration['createdIp'] = '';

        $user = $this->getUserService()->register(
            $registration
        );
        $this->getUserService()->updateUserProfile($user['id'], ['mobile' => $userInfo['phone']]);

        return $this->getUserDao()->update($user['id'], ['scrmUuid' => $userInfo['unionId'], 'verifyMobile' => $userInfo['phone']]);
    }

    private function filterQuery($query)
    {
        if (!ArrayToolkit::requireds($query, [
            'user_token',
            'receipt_token',
            'order_id',
        ])) {
            throw new InvalidArgumentException('参数不正确！');
        }
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->getBiz()->dao('User:UserDao');
    }

    /**
     * @return ScrmService
     */
    protected function getScrmSdk()
    {
        $biz = $this->getBiz();

        return $biz['ESCloudSdk.scrm'];
    }

    protected function getRandomString($length, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $s = '';
        $cLength = strlen($chars);

        while (strlen($s) < $length) {
            $s .= $chars[mt_rand(0, $cLength - 1)];
        }

        return $s;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
