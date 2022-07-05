<?php

namespace MarketingMallBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use AppBundle\Controller\BaseController;
use Biz\System\Service\SettingService;
use Firebase\JWT\JWT;
use Http\Discovery\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;

class CallbackController extends BaseController
{
    public function indexAction(Request $request)
    {
        $query = $this->filterRequest($request);
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
        $query = $request->query->all();
        $requiredFields = [
            'targetId',
            'targetType',
            'token',
        ];
        if (!ArrayToolkit::parts($query, $requiredFields)) {
            throw new InvalidArgumentException('参数不正确！');
        }
        $mallSettings = $this->getSettingService()->get('marketing_mall', []);
        try {
            $result = JWT::decode($query['token'], $mallSettings['secret_key'], ['HS256']);
            $access_key = $mallSettings['access_key'];
            $query['userId'] = $result->userId;
        } catch (\RuntimeException $e) {
            throw new NotFoundException('token error！');
        }
        if ($result->access_key !== $access_key) {
            throw new NotFoundException('token auth error！');
        }

        return $query;
    }

    protected function getUrlWithTarget($query)
    {
        $routes = [
            'course' => 'my_course_show',
            'classroom' => 'classroom_courses',
            'item_bank_exercise' => 'my_item_bank_exercise_show',
        ];
        $param = 'classroom' === $query['targetType'] ? ['classroomId' => $query['targetId']] : ['id' => $query['targetId']];
        if ('2' === $this->setting('wap.version') && DeviceToolkit::isMobileClient()) {
            $routes['classroom'] = 'classroom_show';
            $routes['item_bank_exercise'] = 'item_bank_exercise_show';
            $token = $this->getUserService()->makeToken('mobile_login', $query['userId'], time() + 3600 * 24 * 30, []);
            $param = [
                'id' => $query['targetId'],
                'loginToken' => $token,
            ];
        }
        $route = $routes[$query['targetType']] ?? 'homepage';

        return $this->generateUrl($route, $param);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
