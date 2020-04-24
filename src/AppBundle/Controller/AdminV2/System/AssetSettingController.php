<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\FileToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\System\SettingException;
use Biz\User\Service\AuthService;
use Biz\User\Service\UserFieldService;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use VipPlugin\Biz\Vip\Service\LevelService;

class AssetSettingController extends BaseController
{
    public function paymentAction(Request $request)
    {
        $payment = $this->getSettingService()->get('payment', array());
        $default = array(
            'enabled' => 0,
            'disabled_message' => '由于网校未开通任一支付功能，当前商品不支持购买，请联系网校开通支付功能后再进行购买。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 0,
            'tenpay_key' => '',
            'tenpay_secret' => '',
            'wxpay_enabled' => 0,
            'wxpay_appid' => '',
            'wxpay_account' => '',
            'wxpay_key' => '',
            'wxpay_secret' => '',
            'llpay_enabled' => 0,
            'llpay_key' => '',
            'llpay_accessKey' => '',
            'llpay_secretKey' => '',
        );
        $default['wxpay_mp_secret'] = $this->getWeixinMpFile();

        $payment = array_merge($default, $payment);
        if ('POST' == $request->getMethod()) {
            $payment = $request->request->all();
            $payment = ArrayToolkit::trim($payment);
            if (!$payment['enabled']) {
                $payment['alipay_enabled'] = 0;
                $payment['wxpay_enabled'] = 0;
                $payment['llpay_enabled'] = 0;
            }
            $payment['disabled_message'] = empty($payment['disabled_message']) ? $default['disabled_message'] : $payment['disabled_message'];

            $formerPayment = $this->getSettingService()->get('payment');

            $payment = array_merge($formerPayment, $payment);

            $this->getSettingService()->set('payment', $payment);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/asset-setting/payment.html.twig', array(
            'payment' => $payment,
        ));
    }

    public function refundAction(Request $request)
    {
        $refundSetting = $this->getSettingService()->get('refund', array());
        $default = array(
            'maxRefundDays' => 0,
            'applyNotification' => '',
            'successNotification' => '',
            'failedNotification' => '',
        );

        $refundSetting = array_merge($default, $refundSetting);

        if ('POST' == $request->getMethod()) {
            $refundSetting = $request->request->all();
            $this->getSettingService()->set('refund', $refundSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/asset-setting/refund.html.twig', array(
            'refundSetting' => $refundSetting,
        ));
    }

    public function coinAction(Request $request)
    {
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

    public function coinModelAction(Request $request)
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

            return $this->render('admin-v2/system/asset-setting/coin/coin-course-set.html.twig', array(
                'set' => $set,
                'items' => $courseSets,
            ));
        }

        if ($request->query->get('set')) {
            $coinSettings = $request->query->get('set');
        }

        response :

        return $this->render('admin-v2/system/asset-setting/coin/coin-model.html.twig', array(
            'coinSettings' => $coinSettings,
        ));
    }

    public function coinPictureAction(Request $request)
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

    public function coinPictureRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('coin');
        $setting['coin_picture'] = '';

        $this->getSettingService()->set('coin', $setting);

        return $this->createJsonResponse(true);
    }

    public function coinModelSaveAction(Request $request)
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

        return $this->redirect($this->generateUrl('admin_v2_coin_model'));
    }

    public function coinTableAjaxAction(Request $request)
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

        return $this->render('admin-v2/system/asset-setting/coin/coin-table-setting.html.twig', array(
            'type' => $conditions['type'],
            'items' => $items,
            'set' => $set,
        ));
    }

    private function getWeixinMpFile()
    {
        $dir = $this->container->getParameter('kernel.root_dir').'/../web';
        $mp_secret = array_map('file_get_contents', glob($dir.'/MP_verify_*.txt'));

        return implode($mp_secret);
    }

    protected function updateWeixinMpFile($val)
    {
        $dir = $this->container->getParameter('kernel.root_dir').'/../web';
        array_map('unlink', glob($dir.'/MP_verify_*.txt'));
        if (!empty($val)) {
            file_put_contents($dir.'/MP_verify_'.$val.'.txt', $val);
        }
    }

    protected function settingsRenderedPage($coinSettings)
    {
        return $this->render('admin-v2/system/asset-setting/coin/coin-settings.html.twig', array(
            'coin_settings_posted' => $coinSettings,
        ));
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

        $name = "{$this->container->getParameter('topxia.upload.public_url_path')}/coin/{$imageName}";
        $path = ltrim($name, '/');

        return array($image, $path);
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }
}
