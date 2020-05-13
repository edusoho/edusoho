<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\MathToolkit;
use Biz\Account\Service\AccountProxyService;
use Biz\System\SettingException;
use Imagine\Image\Box;
use Imagine\Gd\Imagine;
use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CoinController extends BaseController
{
    public function settingsAction(Request $request)
    {
        $postedParams = $request->request->all();

        $coinSettingsSaved = $this->getSettingService()->get('coin', array());

        $default = array(
            'coin_enabled' => 0,
            'cash_model' => 'none',
            'cash_rate' => 1,
            'coin_name' => '虚拟币',
            'coin_content' => '',
            'coin_picture' => '',
            'coin_picture_50_50' => '',
            'coin_picture_30_30' => '',
            'coin_picture_20_20' => '',
            'coin_picture_10_10' => '',
            'charge_coin_enabled' => '',
        );
        $coinSettingsSaved = array_merge($default, $coinSettingsSaved);

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();

            $coinSettingsPosted = ArrayToolkit::parts($fields, array(
                'coin_enabled',
                'cash_model',
                'cash_rate',
                'coin_name',
                'coin_content',
                'coin_picture',
                'coin_picture_50_50',
                'coin_picture_30_30',
                'coin_picture_20_20',
                'coin_picture_10_10',
                'charge_coin_enabled',
            ));

            $coinSettings = array_merge($coinSettingsSaved, $coinSettingsPosted);

            $coinSettings['coin_content'] = $this->purifyHtml($coinSettings['coin_content'], true);

            $this->getSettingService()->set('coin', $coinSettings);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->settingsRenderedPage($coinSettingsPosted);
        }

        return $this->settingsRenderedPage($coinSettingsSaved);
    }

    protected function savePicture(Request $request, $size)
    {
        $file = $request->files->get('coin_picture');
        $filename = 'logo_'.time().'.'.$file->getClientOriginalExtension();
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/coin";

        $pictureFilePath = $directory.'/'.$filename;
        $pathinfo = pathinfo($pictureFilePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($pictureFilePath);

        $image = $rawImage->copy();
        $image->resize(new Box($size, $size));
        $filePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_{$size}-{$size}.{$pathinfo['extension']}";
        $imageName = "{$pathinfo['filename']}_{$size}-{$size}.{$pathinfo['extension']}";
        $image = $image->save($filePath, array('quality' => 100));

        $coin = $this->getSettingService()->get('coin', array());
        $name = "{$this->container->getParameter('topxia.upload.public_url_path')}/coin/{$imageName}";
        $path = ltrim($name, '/');

        return array($image, $path);
    }

    public function modelAction(Request $request)
    {
        $coinSettings = $this->getSettingService()->get('coin', array());

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            if ('none' == $set['cash_model']) {
                $coinSettings['cash_model'] = 'none';
                $coinSettings['price_type'] = 'RMB';
                $coinSettings['cash_rate'] = $set['cash_rate'];
                $coinSettings['coin_enabled'] = 0;

                $this->getSettingService()->set('coin', $coinSettings);
                $this->setFlashMessage('success', 'site.save.success');
                goto response;
            }

            $courseSets = $this->getCourseSetService()->searchCourseSets(array(
                'parentId' => 0,
                'maxCoursePrice_GT' => 0,
            ), array('updatedTime' => 'desc'), 0, PHP_INT_MAX);

            return $this->render('admin/coin/coin-course-set.html.twig', array(
                'set' => $set,
                'items' => $courseSets,
            ));
        }

        if ($request->query->get('set')) {
            $coinSettings = $request->query->get('set');
        }

        response :

            return $this->render('admin/coin/coin-model.html.twig', array(
            'coinSettings' => $coinSettings,
        ));
    }

    public function tableAjaxAction(Request $request)
    {
        $conditions = $request->query->all();
        $type = $conditions['type'];
        $set = $conditions['set'];

        if ('course' == $type) {
            $items = $this->getCourseSetService()->searchCourseSets(array(
                'maxCoursePrice_GT' => '0.00',
                'parentId' => 0,
            ), array('updatedTime' => 'desc'), 0, PHP_INT_MAX);
        } elseif ('classroom' == $type) {
            $items = $this->getClassroomService()->searchClassrooms(
                array('private' => 0, 'price_GT' => '0.00'),
                array('createdTime' => 'DESC'),
                0,
                PHP_INT_MAX
            );
        } elseif ('vip' == $type) {
            // todo
            $items = $this->getLevelService()->searchLevels(array('enable' => 1), array('seq' => 'asc'), 0, PHP_INT_MAX);
        }

        return $this->render('admin/coin/coin-table-setting.html.twig', array(
            'type' => $conditions['type'],
            'items' => $items,
            'set' => $set,
        ));
    }

    public function modelSaveAction(Request $request)
    {
        $coinSettings = $this->getSettingService()->get('coin', array());

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            $coinSettings['coin_enabled'] = 1;
            $coinSettings['cash_rate'] = $data['cash_rate'];

            if ('deduction' == $data['cash_model']) {
                $coinSettings['price_type'] = 'RMB';
                $coinSettings['cash_model'] = 'deduction';

                if (isset($data['item-rate'])) {
                    $this->updateMaxRate($data);
                }
            } else {
                $coinSettings['price_type'] = 'Coin';
                $coinSettings['cash_model'] = 'currency';
            }

            $this->getSettingService()->set('coin', $coinSettings);
        }

        $this->setFlashMessage('success', 'site.save.success');

        return $this->redirect($this->generateUrl('admin_coin_model'));
    }

    protected function updateMaxRate($data)
    {
        $type = $data['type'];
        $data = $data['item-rate'];

        if ('course' == $type) {
            foreach ($data as $key => $value) {
                $this->getCourseSetService()->updateMaxRate($key, $value);
            }
        } elseif ('classroom' == $type) {
            foreach ($data as $key => $value) {
                $this->getClassroomService()->updateClassroom($key, array('maxRate' => $value));
            }
        } elseif ('vip' == $type) {
            foreach ($data as $key => $value) {
                $this->getLevelService()->updateLevel($key, array('maxRate' => $value));
            }
        }
    }

    public function pictureAction(Request $request)
    {
        $file = $request->files->get('coin_picture');

        if (!FileToolkit::isImageFile($file)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $filename = 'logo_'.time().'.'.$file->getClientOriginalExtension();
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/coin";
        $file = $file->move($directory, $filename);

        $size = getimagesize($file);
        $width = $size[0];
        $height = $size[1];

        if ($width < 50 || $height < 50 || $width != $height) {
            $this->createNewException(SettingException::COIN_IMG_SIZE_LIMIT());
        }

        list($coin_picture_50_50, $url_50_50) = $this->savePicture($request, 50);
        list($coin_picture_30_30, $url_30_30) = $this->savePicture($request, 30);
        list($coin_picture_20_20, $url_20_20) = $this->savePicture($request, 20);
        list($coin_picture_10_10, $url_10_10) = $this->savePicture($request, 10);

        $coin = $this->getSettingService()->get('coin', array());

        $coin['coin_picture'] = $coin['coin_picture_50_50'] = $url_50_50;
        $coin['coin_picture_30_30'] = $url_30_30;
        $coin['coin_picture_20_20'] = $url_20_20;
        $coin['coin_picture_10_10'] = $url_10_10;

        $this->getSettingService()->set('coin', $coin);

        $response = array(
            'path' => $coin['coin_picture'],
            'path_50_50' => $coin['coin_picture_50_50'],
            'path_30_30' => $coin['coin_picture_30_30'],
            'path_20_20' => $coin['coin_picture_20_20'],
            'path_10_10' => $coin['coin_picture_10_10'],
            'url' => $this->container->get('assets.packages')->getUrl($coin['coin_picture']),
            'coin_picture_50_50' => $this->container->get('assets.packages')->getUrl($coin['coin_picture_50_50']),
            'coin_picture_30_30' => $this->container->get('assets.packages')->getUrl($coin['coin_picture_30_30']),
            'coin_picture_20_20' => $this->container->get('assets.packages')->getUrl($coin['coin_picture_20_20']),
            'coin_picture_10_10' => $this->container->get('assets.packages')->getUrl($coin['coin_picture_10_10']),
        );

        return new Response(json_encode($response));
    }

    public function pictureRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('coin');
        $setting['coin_picture'] = '';

        $this->getSettingService()->set('coin', $setting);

        return $this->createJsonResponse(true);
    }

    public function userRecordsAction(Request $request)
    {
        $sort = $request->query->get('sort', 'amount');
        $direction = $request->query->get('direction', 'DESC');
        $conditions['except_user_id'] = 0;

        $fields = $request->query->all();

        if (!empty($fields)) {
            $convertCondition = $this->convertFiltersToCondition($fields);
            $conditions = array_merge($conditions, $convertCondition);
        }

        $schoolBalance = $this->getAccountProxyService()->getUserBalanceByUserId(0);

        if (isset($conditions['user_id'])) {
            if (0 == $conditions['user_id']) {
                $users = array();
                $balances = array();
                goto response;
            }
            $user = $this->getUserService()->getUser($conditions['user_id']);
            $users = array($conditions['user_id'] => $user);
            $balances = array();
            $balances[] = $this->getAccountProxyService()->getUserBalanceByUserId($conditions['user_id']);

            response :

                return $this->render('admin/coin/coin-user-records.html.twig', array(
                'schoolBalance' => $schoolBalance,
                'balances' => $balances,
                'users' => $users,
            ));
        }

        $systemUser = $this->getUserService()->getUserByType('system');

        $paginator = new Paginator(
            $request,
            $this->getAccountProxyService()->countBalances(
                array(
                    'except_user_ids' => array(0, $systemUser['id']),
                )
            ),
            20
        );
        $balances = $this->getAccountProxyService()->searchBalances(
            array(
                'except_user_ids' => array(0, $systemUser['id']),
            ),
            array($sort => $direction),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($balances, 'user_id');

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/coin/coin-user-records.html.twig', array(
            'schoolBalance' => $schoolBalance,
            'balances' => $balances,
            'paginator' => $paginator,
            'users' => $users,
        ));
    }

    public function flowDetailAction(Request $request)
    {
        $userId = $request->query->get('userId');
        $conditions['except_user_id'] = 0;
        $conditions['amount_type'] = 'coin';
        $conditions['user_id'] = $userId;

        $paginator = new Paginator(
            $request,
            $this->getAccountProxyService()->countCashflows($conditions),
            20
        );

        $cashes = $this->getAccountProxyService()->searchCashflows(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($cashes as &$cash) {
            $cash = MathToolkit::multiply($cash, array('amount'), 0.01);
        }

        $user = $this->getUserService()->getUser($userId);

        return $this->render('admin/coin/flow-detail-modal.html.twig', array(
            'user' => $user,
            'cashes' => $cashes,
            'paginator' => $paginator,
        ));
    }

    protected function settingsRenderedPage($coinSettings)
    {
        return $this->render('admin/coin/coin-settings.html.twig', array(
            'coin_settings_posted' => $coinSettings,
        ));
    }

    protected function convertFiltersToCondition($condition)
    {
        if (!empty($condition['keyword'])) {
            $user = $this->getUserService()->getUserByNickname($condition['keyword']);
            $condition['user_id'] = $user ? $user['id'] : 0;
            unset($condition['keyword']);
        }

        return $condition;
    }

    protected function filterConditionBill($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);

            if ($user) {
                $conditions['userId'] = $user['id'];
            } else {
                $conditions['userId'] = -1;
            }
        }

        $conditions['cashType'] = 'RMB';
        $conditions['startTime'] = 0;
        $conditions['endTime'] = time();

        if (!empty($conditions['lastHowManyMonths'])) {
            switch ($conditions['lastHowManyMonths']) {
                case 'oneWeek':
                    $conditions['startTime'] = $conditions['endTime'] - 7 * 24 * 3600;
                    break;
                case 'twoWeeks':
                    $conditions['startTime'] = $conditions['endTime'] - 14 * 24 * 3600;
                    break;
                case 'oneMonth':
                    $conditions['startTime'] = $conditions['endTime'] - 30 * 24 * 3600;
                    break;
                case 'twoMonths':
                    $conditions['startTime'] = $conditions['endTime'] - 60 * 24 * 3600;
                    break;
                case 'threeMonths':
                    $conditions['startTime'] = $conditions['endTime'] - 90 * 24 * 3600;
                    break;
            }
        }

        return $conditions;
    }

    protected function filterCondition($conditions)
    {
        if (isset($conditions['keywordType'])) {
            if ('userName' == $conditions['keywordType']) {
                $conditions['keywordType'] = 'user_id';
                $userFindbyNickName = $this->getUserService()->getUserByNickname($conditions['keyword']);
                $conditions['keyword'] = $userFindbyNickName ? $userFindbyNickName['id'] : -1;
            }
        }

        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (isset($conditions['createdTime'])) {
            $conditions['timeType'] = $conditions['createdTime'];

            unset($conditions['createdTime']);
        }

        return $conditions;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return AccountProxyService
     */
    protected function getAccountProxyService()
    {
        return $this->createService('Account:AccountProxyService');
    }

    protected function getAccountService()
    {
        return $this->createService('Pay:AccountService');
    }
}
