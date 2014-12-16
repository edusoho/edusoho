<?php
namespace Topxia\Service\Util;

class PushService{

	private static $connect = null;
	private static  $socket = null;
	private static  $host = "192.168.11.79";
	private static  $port = 9999;
	private static  $version = 1;
	private static  $appd = 1;
	private static  $typeid = 32; // 自定义消息
	private $uuid = "";
	private $data = "";
	private $outMsg = "";

	public static function sendMsg($userId, $data){
		$this->uuid = $userId;
		$this->data = $data;
		$this->initSocket();
		$out = "";
		if($this->connect){
			$this->sendHeader();
			$this->sendData();
			$out = $this->readStream();
		}
		return $out;
	}

	private function initSocket(){
		if($this->socket == null){
			$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		}
		if($this->connect == null){
			$this->connect = socket_connect($this->socket, $this->host,$this->port);
		}
	}

	private function sendHeader()
	{
		$this->outMsg .= pack('c', $this->version);
		socket_write($this->socket, $this->outMsg);
	}

	private function sendData()
	{
		$this->sendHeand();
		$this->outMsg = null;
		$this->outMsg .= pack('c',$this->appid);
		$this->outMsg .= pack('c',$this->typeid);
		$this->outMsg .= $this->uuidToByte($this->uuid);
	    $this->outMsg .= $this->mkDataStream($this->data);
		socket_write($this->socket, $this->outMsg);
		
	}

	private function uuidToByte($uuid)
	{
		$str = array_map('ord',str_split($uuid));
		$result = "";
		for($i = 0 ;$i < count($str);$i += 2){
			$s = chr($str[$i]) . chr($str[$i + 1]);
			$result .= pack('c',hexdec($s));
		}
		return $result;
	}
	
	private function mkDataStream($data)
	{
		$len = strlen($data) ;
		$result = "";
	    $result .= pack('n',$len);
	    $str = array_map('ord',str_split($data));
	    foreach($str as $vo){  
	        $result .= pack('c',$vo);  
	    }
		return $result;
	}

	private function readStream()
	{
		$out = socket_read($this->socket,1024,PHP_BINARY_READ);
		return $out;
	}
}



