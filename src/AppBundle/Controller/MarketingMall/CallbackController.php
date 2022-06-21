<?php

namespace AppBundle\Controller\MarketingMall;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\System\Service\SettingService;
use Firebase\JWT\JWT;
use Http\Discovery\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;

class CallbackController extends BaseController
{
    public function indexAction(Request $request)
    {
        $this->filterRequest($request);
        $query = $request->query->all();
        $user = $this->getUserService()->getUser($query['userId']);
        if (empty($user)) {
            throw new NotFoundException('user not found！');
        }
        try {
            $this->authenticateUser($user);
            $url = $this->getUrlWithTarget($query);
        } catch (\Exception $e) {
            $this->authenticateUser([
                'id' => 0,
                'nickname' => '游客',
                'currentIp' => '',
                'roles' => [],
                'email' => '',
                'locked' => 0,
                'type' => '',
                'password' => '',
            ]);

            return $this->createJsonResponse(['message' => $e->getMessage()]);
        }

        return $this->redirect($url);
    }

    private function filterRequest($request)
    {
        $tokenInHeader = $request->headers->get('Mall-Auth-Token');
        $query = $request->query->all();
        $requiredFields = [
            'targetId',
            'targetType',
            'userId'
        ];
        if (empty($tokenInHeader) || !ArrayToolkit::parts($query, $requiredFields)) {
            throw new InvalidArgumentException('参数不正确！');
        }
        $mallSettings = $this->getSettingService()->get('marketing_mall', []);
        $storages = $this->getSettingService()->get('storages', []);
        try {
            if (empty($mallSettings['secret_key'])) {
                $result = JWT::decode($tokenInHeader, $storages['cloud_secret_key'], ['HS256']);
                $access_key = $storages['cloud_access_key'];
            } else {
                $result = JWT::decode($tokenInHeader, $mallSettings['secret_key'], ['HS256']);
                $access_key = $mallSettings['access_key'];
            }
        } catch (\RuntimeException $e) {
            throw new NotFoundException('token error！');
        }
        if ($result->access_key !== $access_key) {
            throw new NotFoundException('token auth error！');
        }
    }

    protected function getUrlWithTarget($query)
    {
        $route = 'homepage';
        if ('course' === $query['targetType']) {
            $route = 'my_course_show';
            $isMember = $this->getCourseMemberService()->getCourseMember($query['targetId'], $query['userId']);
        }
        if ('classroom' === $query['targetType']) {
            $route = 'classroom_courses';
            $isMember = $this->getClassroomService()->getClassroomMember($query['targetId'], $query['userId']);
        }
        if ('item_bank_exercise' === $query['targetType']) {
            $route = 'my_item_bank_exercise_show';
            $isMember = $this->getItemBankExerciseMemberService()->getExerciseMember($query['targetId'], $query['userId']);
        }
        if (empty($isMember)) {
            throw new NotFoundException('is not member');
        }
        $param = ['id' => $query['targetId']];
        if (2 == $this->setting('wap.version') && DeviceToolkit::isMobileClient()) {
            $token = $this->getUserService()->makeToken('mobile_login', $query['userId'], time() + 3600 * 24 * 30, []);
            $param['loginToken'] = $token;
        }

        return $this->generateUrl($route, $param);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getItemBankExerciseMemberService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseMemberService');
    }
}
