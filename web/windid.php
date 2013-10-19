<?php

require_once __DIR__ . '/windid_client/src/windid/WindidApi.php';//引入windid接口类
require_once __DIR__ . '/windid_client/src/windid/service/base/WindidUtility.php'; //引入windid工具库

$_windidkey = getInput('windidkey', 'get');

$_time = (int)getInput('time', 'get');
$_clentid = (int)getInput('clientid', 'get');
$time = Pw::getTime();
if (WindidUtility::appKey(WINDID_CLIENT_ID, $_time, WINDID_CLIENT_KEY, $_GET, $_POST) != $_windidkey) showMessage('fail'); //对密钥进行验证
if ($time - $_time > 120) showMessage('timeout'); //检查通知是否超时
                
$operation = (int)getInput('operation', 'get');
list($method, $args) = getMethod($operation);
if (!$method) showMessage('fail');
$notify = new notify();  //定义一个通知处理类 在这时定义为下一步所示的notify
if(!method_exists($notify, $method)) showMessage('success');//不指定的方法，默认返回成功状态

$result = call_user_func_array(array($notify,$method), getInput($args,'request'));


if ($result == true) showMessage('success');
showMessage('fail');
                
function getInput($key, $method = 'get') {
    if (is_array($key)) {
            $result = array();
            foreach ($key as $_k=>$_v) {
                $result[$_k] = getInput($_v, $method);
            }
            return $result;
    }
    switch ($method) {
        case 'get':
          return isset($_GET[$key]) ? $_GET[$key] : null;
        case 'post':
          return isset($_POST[$key]) ? $_POST[$key] : null; 
        case 'request':
          return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;   
       default:
            return null;
    }
}
/**
 * windid只接收两种返回状态‘success’ 和'fail'，并在windid后台的“消息队列”里显示
 */
function showMessage($message = 'success') {
    echo $message;
    exit();
}
/**
 *根据操作代表获取操作方法，获取参数
 */
function getMethod($operation) {
    $config = include 'windid_client/src/windid/service/base/WindidNotifyConf.php';  //在这个文件中，定义了通知的接口类型，接收参数
    $method = isset($config[$operation]['method']) ? $config[$operation]['method'] : '';
    $args = isset($config[$operation]['args']) ? $config[$operation]['args'] : array();
    return array($method, $args);
}


class notify
{
        
    public function test($uid) {
        return $uid ? true : false;
    }
            
            
    public function addUser($uid) {
        $api = WindidApi::api('user');
        $user = $api->getUser($uid);
            //客户端系统处理添加新用户
         return true;
    }
            
    public function editUser($uid) {
        global $cfg_ml,$dsql;
        $api = WindidApi::api('user');
        $user = $api->getUser($uid);
          //客户端系统处理修改用户信息
        return true;
    }
        
    public function synLogin($uid) {
        return true;
    }
            
    public function synLogout($uid) {
        return true;
    }
}