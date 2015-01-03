<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
 
class CoinController extends BaseController
{
    public function settingsAction(Request $request)
    {
        $postedParams = $request->request->all();

        $coinSettingsPosted = $this->getSettingService()->get('coin',array());

        $coinSettingsSaved = $coinSettingsPosted;
        $default = array(
          'coin_enabled' => 0,
          'coin_name' => '虚拟币',
          'cash_rate' => 10,
          'coin_consume_range_and_present' => array(array(0,0))
        );
        $coinSettingsPosted = array_merge($default, $coinSettingsPosted);
      
        if ($request->getMethod() == 'POST') {
        $coinSettingsPosted = null;

        $coinSettingsPosted['coin_enabled'] = $request->request->get("coin_enabled");
        $coinSettingsPosted['coin_name'] = $request->request->get("coin_name");
        $this->getSettingService()->set('coin', $coinSettingsPosted);
        $this->getLogService()->info('system', 'update_settings', "更新Coin虚拟币设置", $coinSettingsPosted);
        $this->setFlashMessage('success', '虚拟币设置已保存！');      
        }

        return $this->settingsRenderedPage($coinSettingsPosted);
    }

    public function  recordsAction(Request $request){
        $fields = $request->query->all();
        $conditions=array();
        if(!empty($fields)){
          $conditions =$fields;
        };
        
        if  (isset($conditions['keywordType'])) {
          if ($conditions['keywordType'] == 'userName'){
            $conditions['keywordType'] = 'userId';
            $userFindbyNickName = $this->getUserService()->getUserByNickname($conditions['keyword']);
            $conditions['keyword'] = $userFindbyNickName? $userFindbyNickName['id']:-1;
          }
        }
        if (isset($conditions['keywordType'])) {
          $conditions[$conditions['keywordType']] = $conditions['keyword'];
          unset($conditions['keywordType']);
          unset($conditions['keyword']);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCashService()->searchFlowsCount($conditions),
            20
          );

        $cashes=$this->getCashService()->searchFlows(
            $conditions,
            array('createdTime','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
          );

        $userIds =  ArrayToolkit::column($cashes, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        
        return $this->render('TopxiaAdminBundle:Coin:coin-records.html.twig',array(
            'users'=>$users,
            'cashes'=>$cashes,
            'paginator'=>$paginator,
          ));
    }

    public function  ordersAction(Request $request){

        $fields = $request->query->all();
        $conditions=array();
        if(!empty($fields)){
          $conditions =$fields;
        };
        if  (isset($conditions['keywordType'])) {
          if ($conditions['keywordType'] == 'userName'){
            $conditions['keywordType'] = 'userId';
            $userFindbyNickName = $this->getUserService()->getUserByNickname($conditions['keyword']);
            $conditions['keyword'] = $userFindbyNickName? $userFindbyNickName['id']:-1;
          }
        }
        if (isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCashOrdersService()->searchOrdersCount($conditions),
            20
          );

        $orders=$this->getCashOrdersService()->searchOrders(
            $conditions,
            array('createdTime','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
          );

        $userIds =  ArrayToolkit::column($orders, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaAdminBundle:Coin:coin-orders.html.twig',array(
            'users'=>$users,
            'orders'=>$orders,
            'paginator'=>$paginator,
          ));
    }

    protected function settingsRenderedPage($coinSettings)
    {
      return $this->render('TopxiaAdminBundle:Coin:coin-settings.html.twig',array(
        'coin_settings_posted' => $coinSettings,
      ));
    }

    public function logsAction($id)
    {
        $order = $this->getCashOrdersService()->getOrder($id);
        $user = $this->getUserService()->getUser($order['userId']);

        $orderLogs = $this->getCashOrdersService()->getLogsByOrderId($order['id']);
        
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orderLogs, 'userId'));
        
        return $this->render('TopxiaAdminBundle:Coin:order-log-modal.html.twig', array(
            'order'=>$order,
            'user'=>$user,
            'orderLogs'=>$orderLogs,
            'users' => $users
        ));
    }

    public function giveCoinAction(Request $request)
    {
        if($request->getMethod()=="POST"){

            $fields=$request->request->all();

            $user=$this->getUserService()->getUserByNickname($fields['nickname']);

            $account=$this->getCashService()->getAccountByUserId($user["id"]);

            if(empty($account)){
                $account=$this->getCashService()->createAccount($user["id"]);
            }

            if($fields['type']=="add"){

                $this->getCashService()->waveCashField($account["id"],$fields['amount']);
                $this->getLogService()->info('cash', 'add_coin', "添加 ".$user['nickname']." {$fields['amount']} 虚拟币", array());

            }else{

                $this->getCashService()->waveDownCashField($account["id"],$fields['amount']);
                $this->getLogService()->info('cash', 'add_coin', "扣除 ".$user['nickname']." {$fields['amount']} 虚拟币", array());
            }
  
        }

        return $this->render('TopxiaAdminBundle:Coin:order-create-modal.html.twig', array());

    }

    // public function adminAction(Request $request)
    // {   
    //     $fields = $request->query->all();
    //     $nickname="";
    //     $conditions=array();

    //     if(isset($fields['nickName']) && $fields['nickName']!= ""){
    //         $nickname =$fields['nickName'];
    //         $user = $this->getUserService()->getUserByNickname($nickname);

    //         if($user){
    //             $conditions=array('userId'=>$user['id']);
    //         }else{
    //             $conditions=array('userId'=>-1);
    //         }
            
    //     }

    //     $paginator = new Paginator(
    //         $this->get('request'),
    //         $this->getCashService()->searchAccountCount($conditions),
    //         20
    //       );

    //     $cashes=$this->getCashService()->searchAccount(
    //         $conditions,
    //         array(),
    //         $paginator->getOffsetCount(),
    //         $paginator->getPerPageCount()
    //       );

    //     $userIds =  ArrayToolkit::column($cashes, 'userId');
    //     $users = $this->getUserService()->findUsersByIds($userIds);

    //     return $this->render('TopxiaAdminBundle:Coin:coin-admin.html.twig', array(
    //         'cashes'=>$cashes,
    //         'users'=>$users,
    //         'paginator'=>$paginator,
    //     ));
    // }

    public function editAction(Request $request,$id)
    {   
        if($request->getMethod()=="POST"){

            $fields=$request->request->all();

            $account=$this->getCashService()->getAccount($id);

            if($account){

                $user=$this->getUserService()->getUser($account['userId']);

                if($fields['type']=="add"){

                $this->getCashService()->waveCashField($id,$fields['amount']);

                $this->getLogService()->info('cash', 'add_coin', "添加 ".$user['nickname']." {$fields['amount']} 虚拟币", array());

                }else{

                    $this->getCashService()->waveDownCashField($id,$fields['amount']);
                    $this->getLogService()->info('cash', 'add_coin', "扣除 ".$user['nickname']." {$fields['amount']} 虚拟币", array());

                }
            }
  
        }

        return $this->render('TopxiaAdminBundle:Coin:order-edit-modal.html.twig', array(
            'id'=>$id,
        ));
    }

    public function checkNicknameAction(Request $request)
    {
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);
        if ($result) {
            $response = array('success' => false, 'message' => '该用户不存在');
        } else {
            $response = array('success' => true, 'message' => '');
        }
        return $this->createJsonResponse($response);
    }

    public function avatarAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $file = $data['avatar'];

                if (!FileToolkit::isImageFile($file)) {
                    return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
                }

                $filenamePrefix = "user_{$user['id']}_";
                $hash = substr(md5($filenamePrefix . time()), -8);
                $ext = $file->getClientOriginalExtension();
                $filename = $filenamePrefix . $hash . '.' . $ext;

                $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
                $file = $file->move($directory, $filename);

                $fileName = str_replace('.', '!', $file->getFilename());

                return $this->redirect($this->generateUrl('settings_avatar_crop', array(
                    'file' => $fileName)
                ));
            }
        }

        $hasPartnerAuth = $this->getAuthService()->hasPartnerAuth();
        if ($hasPartnerAuth) {
            $partnerAvatar = $this->getAuthService()->getPartnerAvatar($user['id'], 'big');
        } else {
            $partnerAvatar = null;
        }

        $fromCourse = $request->query->get('fromCourse');

        return $this->render('TopxiaWebBundle:Settings:avatar.html.twig', array(
            'form' => $form->createView(),
            'user' => $this->getUserService()->getUser($user['id']),
            'partnerAvatar' => $partnerAvatar,
            'fromCourse' => $fromCourse,
        ));
    }

    public function avatarCropAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $filename = $request->query->get('file');
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($currentUser['id'], $pictureFilePath, $options);
            return $this->redirect($this->generateUrl('settings_avatar'));
        }

        try {
            $imagine = new Imagine();
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(270)->heighten(270);
        $pictureUrl = 'tmp/' . $filename;

        return $this->render('TopxiaWebBundle:Settings:avatar-crop.html.twig', array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    protected function getSettingService(){

      return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCashService(){
      
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    protected function getCashOrdersService(){
      
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getLogService() 
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }


}
