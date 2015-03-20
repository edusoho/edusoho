<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Symfony\Component\HttpFoundation\Response;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\Filesystem\Filesystem;
 
class CoinController extends BaseController
{
    public function settingsAction(Request $request)
    {
        $postedParams = $request->request->all();

        $coinSettingsSaved = $this->getSettingService()->get('coin',array());

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
        );
        $coinSettingsSaved = array_merge($default, $coinSettingsSaved);

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            $coinSettingsPosted = ArrayToolkit::parts($fields, array(
                'coin_enabled','cash_model',
                'cash_rate',
                'coin_name',
                'coin_content', 'coin_picture',
                'coin_picture_50_50','coin_picture_30_30',
                'coin_picture_20_20','coin_picture_10_10'
            ));
            
            $this->getSettingService()->set('coin', $coinSettingsPosted);
            $this->getLogService()->info('system', 'update_settings', "更新Coin虚拟币设置", $coinSettingsPosted);
            $this->setFlashMessage('success', '虚拟币设置已保存！');

            return $this->settingsRenderedPage($coinSettingsPosted);

        }

        return $this->settingsRenderedPage($coinSettingsSaved);
    }
    
    private function savePicture(Request $request,$size)
    {
        $file = $request->files->get('coin_picture');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/coin";

        $pictureFilePath = $directory.'/'.$filename;
        $pathinfo = pathinfo($pictureFilePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($pictureFilePath);

        $image = $rawImage->copy();
        $image->resize(new Box($size, $size));
        $filePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_{$size}*{$size}.{$pathinfo['extension']}";
        $imageName = "{$pathinfo['filename']}_{$size}*{$size}.{$pathinfo['extension']}";
        $image = $image->save($filePath, array('quality' => 100));

        $coin = $this->getSettingService()->get('coin',array());
        $name = "{$this->container->getParameter('topxia.upload.public_url_path')}/coin/{$imageName}";
        $path = ltrim($name, '/');

        return array($image,$path);
    }

    public function modelAction(Request $request)
    {   
        $coinSettings = $this->getSettingService()->get('coin',array());

        if($request->getMethod()=="POST"){

            $set=$request->request->all();

            if($set['cash_model']=="none"){

                $coinSettings['cash_model']="none";
                $coinSettings['price_type']="RMB";
                $coinSettings['cash_rate']=$set['cash_rate'];
                $coinSettings['coin_enabled']=0;

                $this->getSettingService()->set('coin', $coinSettings);
                $this->setFlashMessage('success', '虚拟币模式已保存！');
                goto response;

            }

            $courses=$this->getCourseService()->searchCourses(array('notFree'=>"true"),'latest',0,99999);

            return $this->render('TopxiaAdminBundle:Coin:coin-course-set.html.twig',array(
            'set' => $set,
            'courses'=>$courses
            ));

        }

        if($request->query->get('set')){

            $coinSettings=$request->query->get('set');
        }

        response:
        return $this->render('TopxiaAdminBundle:Coin:coin-model.html.twig',array(
            'coinSettings' => $coinSettings,
        ));
    }

    public function modelSaveAction(Request $request)
    {   
        $coinSettings = $this->getSettingService()->get('coin',array());

        if($request->getMethod()=="POST"){

            $data=$request->request->all();

            $coinSettings['coin_enabled']=1;
            $coinSettings['cash_rate']=$data['cash_rate'];
            
            if($data['cash_model']=="deduction"){
                $coinSettings['price_type']="RMB";
                $coinSettings['cash_model']="deduction";
                if (isset($data['course-rmb'])){
                    $this->updateCoursesPrice($data['course-rmb'],$data['cash_rate']);
                }
            }else{
                $coinSettings['price_type']="Coin";
                $coinSettings['cash_model']="currency";
                if (isset($data['course-cash'])){
                    $this->updateCoursesCashPrice($data["course-cash"]);
                }
            }

            $this->getSettingService()->set('coin', $coinSettings);
        }

        $this->setFlashMessage('success', '虚拟币模式已保存！');
        return $this->redirect($this->generateUrl('admin_coin_model', array(
        )));
    }

    private function updateCoursesPrice($data,$rate)
    {   
        foreach ($data as $key => $value) {
            
            $this->getCourseService()->updateCourse($key,array('price'=>$value,'coinPrice'=>$value*$rate));
        }
    }

    private function updateCoursesCashPrice($data)
    {
        foreach ($data as $key => $value) {
           
            $this->getCourseService()->updateCourse($key,array('coinPrice'=>$value));
        }
    }

    public function pictureAction(Request $request)
    {
        $file = $request->files->get('coin_picture');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确，请上传png, gif, jpg格式的图片文件！');
        }

        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/coin";
        $file = $file->move($directory, $filename);

        $size = getimagesize($file);
        $width = $size[0];
        $height = $size[1];
         if ($width < 50 || $height < 50 || $width != $height) {
            throw $this->createAccessDeniedException('图片大小不正确，请上传超过50*50的等比例图片！');
        }

        list($coin_picture_50_50,$url_50_50) = $this->savePicture($request,50);
        list($coin_picture_30_30,$url_30_30)  = $this->savePicture($request,30);
        list($coin_picture_20_20,$url_20_20)  = $this->savePicture($request,20);
        list($coin_picture_10_10,$url_10_10)  = $this->savePicture($request,10);

        $coin = $this->getSettingService()->get('coin',array());

        $coin['coin_picture'] = $coin['coin_picture_50_50'] = $url_50_50;
        $coin['coin_picture_30_30'] = $url_30_30;
        $coin['coin_picture_20_20'] = $url_20_20;
        $coin['coin_picture_10_10'] = $url_10_10;

        $this->getSettingService()->set('coin', $coin);

        $this->getLogService()->info('system', 'update_settings', "更新虚拟币图片", array('coin_picture' => $coin['coin_picture']));

        $response = array(
            'path' => $coin['coin_picture'],
            'path_50_50' => $coin['coin_picture_50_50'],
            'path_30_30' => $coin['coin_picture_30_30'],
            'path_20_20' => $coin['coin_picture_20_20'],
            'path_10_10' => $coin['coin_picture_10_10'],
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($coin['coin_picture']),
            'coin_picture_50_50' =>  $this->container->get('templating.helper.assets')->getUrl($coin['coin_picture_50_50']),
            'coin_picture_30_30' =>  $this->container->get('templating.helper.assets')->getUrl($coin['coin_picture_30_30']),
            'coin_picture_20_20' =>  $this->container->get('templating.helper.assets')->getUrl($coin['coin_picture_20_20']),
            'coin_picture_10_10' =>  $this->container->get('templating.helper.assets')->getUrl($coin['coin_picture_10_10']),
        );

        return new Response(json_encode($response));

    }


    public function pictureRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get("coin");
        $setting['coin_picture'] = '';

        $this->getSettingService()->set('coin', $setting);

        $this->getLogService()->info('system', 'update_settings', "移除虚拟币图片");

        return $this->createJsonResponse(true);
    }


    public function  recordsAction(Request $request){
        $fields = $request->query->all();
        $conditions=array(
            'startTime'=>time()-7*24*3600);

        if(!empty($fields)){
          $conditions =$this->filterCondition($fields);
        };

        $conditions['cashType']="Coin";

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

        if(isset($conditions['type'])){

            switch ($conditions['type']) {
                case 'inflow':
                    $inflow=$this->getCashService()->analysisAmount($conditions);
                    $outflow=0;
                    break;
                case 'outflow':
                    $outflow=$this->getCashService()->analysisAmount($conditions);
                    $inflow=0;
                    break;
                default:
                    $conditions['type']="outflow";
                    $outflow=$this->getCashService()->analysisAmount($conditions);
                    $conditions['type']="inflow";
                    $inflow=$this->getCashService()->analysisAmount($conditions);
                    break;
            }

        }else{

            $conditions['type']="outflow";
            $outflow=$this->getCashService()->analysisAmount($conditions);
            $conditions['type']="inflow";
            $inflow=$this->getCashService()->analysisAmount($conditions);   
        }

        $in=$this->getCashService()->analysisAmount(array('type'=>'inflow','cashType'=>'Coin'));
        $out=$this->getCashService()->analysisAmount(array('type'=>'outflow','cashType'=>'Coin'));
        $amounts=$in-$out;

        $userIds =  ArrayToolkit::column($cashes, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        
        return $this->render('TopxiaAdminBundle:Coin:coin-records.html.twig',array(
            'users'=>$users,
            'cashes'=>$cashes,
            'outflow'=>$outflow,
            'inflow'=>$inflow,
            'amounts'=>$amounts,
            'paginator'=>$paginator,
          ));
    }

    public function userRecordsAction(Request $request)
    {   
        $condition['time']=time()-7*3600*24;
        $condition['type']="";
        $condition['timeType']="oneWeek";
        $condition['orderBY']="desc";
        $condition['searchType']="";
        $condition['keyword']="";
        $condition['sort']="down";
        $condition['flowType']="";

        $fields = $request->query->all();

        if(!empty($fields)){
          $condition =$this->convertFiltersToCondition($fields);
        };

        if(isset($condition['userId'])){

            if($condition['userId'] == 0 ){
                $userIds=array();
                $users=array();
                $condition['userId']="null";
                goto response;
            } 
            

            $userIds=array($condition['userId']);
            $user=$this->getUserService()->getUser($condition['userId']);
            $users=array($condition['userId']=>$user);

            response:
            return $this->render('TopxiaAdminBundle:Coin:coin-user-records.html.twig',array(
              'condition'=>$condition,
              'userIds'=>$userIds,
              'users'=>$users,
            ));
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCashService()->findUserIdsByFlowsCount($condition['type'],$condition['time']),
            20
          );

        $flows=$this->getCashService()->findUserIdsByFlows(
            $condition['type'],$condition['time'],$condition['orderBY'],           
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
          );

        $userIds=ArrayToolkit::column($flows,'userId');

        $users=$this->getUserService()->findUsersByIds($userIds);
        
        return $this->render('TopxiaAdminBundle:Coin:coin-user-records.html.twig',array(
          'paginator'=>$paginator,
          'condition'=>$condition,
          'userIds'=>$userIds,
          'users'=>$users,
          ));
    }
    
    public function flowDetailAction(Request $request)
    {   
        $userId=$request->query->get("userId");
        $timeType=$request->query->get("timeType");

        if(empty($timeType)){
            $timeType="oneWeek";
        }

        $condition['timeType']=$timeType;
        $filter =$this->convertFiltersToCondition($condition);

        $conditions['startTime']=$filter['time'];
        $conditions['cashType']="Coin";
        $conditions['userId']=$userId;

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
        
        $user=$this->getUserService()->getUser($userId);

        return $this->render('TopxiaAdminBundle:Coin:flow-deatil-modal.html.twig', array(
            'user'=>$user,
            'cashes'=>$cashes,
            'paginator'=>$paginator,
            'timeType'=>$timeType,
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

            $account=$this->getCashAccountService()->getAccountByUserId($user["id"]);

            if(empty($account)){
                $account=$this->getCashAccountService()->createAccount($user["id"]);
            }

            if($fields['type']=="add"){

                $this->getCashAccountService()->waveCashField($account["id"],$fields['amount']);
                $this->getLogService()->info('cash', 'add_coin', "添加 ".$user['nickname']." {$fields['amount']} 虚拟币", array());

            }else{

                $this->getCashAccountService()->waveDownCashField($account["id"],$fields['amount']);
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
    //         $this->getCashAccountService()->searchAccountCount($conditions),
    //         20
    //       );

    //     $cashes=$this->getCashAccountService()->searchAccount(
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

            $account=$this->getCashAccountService()->getAccount($id);

            if($account){

                $user=$this->getUserService()->getUser($account['userId']);

                if($fields['type']=="add"){

                $this->getCashAccountService()->waveCashField($id,$fields['amount']);

                $this->getLogService()->info('cash', 'add_coin', "添加 ".$user['nickname']." {$fields['amount']} 虚拟币", array());

                }else{

                    $this->getCashAccountService()->waveDownCashField($id,$fields['amount']);
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

    public function cashBillAction(Request $request)
    {
        if($request->get('nickname')){

            $user=$this->getUserService()->getUserByNickname($request->get('nickname'));
       
            if($user){

                $conditions['userId']=$user['id'];
            }else{

                $conditions['userId']=-1;
            }
        }

        $conditions['cashType'] = 'RMB';
        $conditions['startTime'] = 0; 
        $conditions['endTime'] = time();


        switch ($request->get('lastHowManyMonths')) { 
            case 'oneWeek': 
                $conditions['startTime'] = $conditions['endTime']-7*24*3600; 
                break; 
            case 'twoWeeks': 
                $conditions['startTime'] = $conditions['endTime']-14*24*3600; 
                break; 
            case 'oneMonth': 
                $conditions['startTime'] = $conditions['endTime']-30*24*3600;               
                break;     
            case 'twoMonths': 
                $conditions['startTime'] = $conditions['endTime']-60*24*3600;               
                break;   
            case 'threeMonths': 
                $conditions['startTime'] = $conditions['endTime']-90*24*3600;               
                break;  
        } 
        
        $paginator = new Paginator(
            $request,
            $this->getCashService()->searchFlowsCount($conditions),
            20
        );

        $cashes = $this->getCashService()->searchFlows(
            $conditions,
            array('ID','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $userIds=ArrayToolkit::column($cashes,"userId");
        $users=$this->getUserService()->findUsersByIds($userIds);

        $conditions['type']  = 'inflow';      
        $amountInflow = $this->getCashService()->analysisAmount($conditions);

        $conditions['type']  = 'outflow'; 
        $amountOutflow = $this->getCashService()->analysisAmount($conditions);

        return $this->render('TopxiaAdminBundle:Coin:cash-bill.html.twig',array(
            'cashes' => $cashes,
            'paginator' => $paginator,
            'users'=>$users,
            'amountInflow' => $amountInflow?:0,
            'amountOutflow' => $amountOutflow?:0            
          
        ));   
    }

    private function convertFiltersToCondition($condition)
    {   
        $condition['time']=time()-7*3600*24;
        $condition['type']="";
        $condition['orderBY']="desc";
        $keyword="";

        if(isset($condition['searchType'])){

            if(isset($condition['keyword'])){

                $keyword=$condition['keyword'];
            }

            if($keyword !=""){
                switch ($condition['searchType']) {
                case 'nickname':
                    $user=$this->getUserService()->getUserByNickname($keyword);
                    $condition['userId']=$user ? $user['id'] : 0 ;
                    break;
                case 'email':
                    $user=$this->getUserService()->getUserByEmail($keyword);
                    $condition['userId']=$user ? $user['id'] : 0 ;
                    break;                
                default:
                    break;
                }
            }

        }else{
            
            $condition['searchType']="";
            $condition['keyword']="";
        }

        if(isset($condition['timeType']))
        {
            switch ($condition['timeType']) {
                case 'oneWeek':
                    $condition['time']=time()-7*3600*24;
                    break;
                case 'oneMonth':
                    $condition['time']=time()-30*3600*24;
                    break;                
                case 'threeMonths':
                    $condition['time']=time()-90*3600*24;
                    break;
                case 'all':
                    $condition['time']=0;
                    break;
                default:
                    break;
            }
        }else{
            $condition['timeType']="oneWeek";
        }

        if(isset($condition['sort'])){

            switch ($condition['sort']) {
                case 'up':
                    $condition['orderBY']="ASC";
                    break;
                case 'down':
                    $condition['orderBY']="DESC";
                    break;               
                default:
                    break;
            }  
        }else{
            $condition['sort']="down";
        }

        if(isset($condition['flowType'])){

            switch ($condition['flowType']) {
                case 'in':
                    $condition['type']="inflow";
                    break;
                case 'out':
                    $condition['type']="outflow";
                    break;            
                default:
                    break;
            }  
        }else{
            $condition['flowType']="";
        }


        return $condition;
    }

    private function processPrice($priceType, $cashRate)
    {
        if($priceType=="RMB") {
            $this->getCourseService()->updatePrice($cashRate);
        } else if($priceType=="Coin" ) {
            $this->getCourseService()->updateCoinPrice($cashRate);
        }
    }

    private function filterCondition($conditions)
    {
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

        if(isset($conditions['createdTime'])){

            switch ($conditions['createdTime']) {
                case 'oneWeek':
                    $conditions['startTime']=time()-7*24*3600;
                    break;
                case 'oneMonth':
                    $conditions['startTime']=time()-30*24*3600;
                    break;
                case 'threeMonths':
                    $conditions['startTime']=time()-90*24*3600;
                    break;
                case 'all':
                    break;
                default:
                    break;
            }
            unset($conditions['createdTime']);

        }else{

            $conditions['startTime']=time()-7*24*3600;
        }

        return $conditions;
    }

    protected function getSettingService(){

      return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getCashService(){
      
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    protected function getCashAccountService(){
        return $this->getServiceKernel()->createService('Cash.CashAccountService');
    }

    protected function getCashOrdersService(){
      
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getLogService() 
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }




}
