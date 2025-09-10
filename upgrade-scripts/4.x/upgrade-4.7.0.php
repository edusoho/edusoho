<?php

use Symfony\Component\Filesystem\Filesystem;

 class EduSohoUpgrade extends AbstractUpdater
 {
    protected $time ;

    protected $num = 0;

    public function update()
    {
      $this->time = date('YmdHis');
      $this->getConnection()->beginTransaction();
      try{
          $this->proccess();
          $this->proccessCashOrders();
          $this->proccessOrderRefund();

          $this->getConnection()->commit();
      } catch(\Exception $e) {
          $this->getConnection()->rollback();
          throw $e;
      }
    }

    private function proccessOrderRefund()
    {
      $sql = "SELECT o.* FROM  `order_refund` r,  `orders` o WHERE r.status =  'success' AND o.id = r.orderId AND o.amount>0 and o.status = 'cancelled' and o.paidTime > 0;";
      $orders = $this->getConnection()->fetchAll($sql, array());

      if(empty($orders) || count($orders)==0){
        return ;
      }

      foreach ($orders as $key => $order) {

        $currentTime = date('YmdHis');
        if($this->time != $currentTime){
          $this->time = $currentTime;
          $this->num = 0;
        } else {
          $this->num++;
        }

        $number = sprintf("%05d", $this->num);

        $cashFlow = array(
          "userId"=>$order["userId"],
          "sn"=> ($this->time . $number),
          "type"=>"inflow",
          "amount"=>$order["amount"],
          "cashType"=>"RMB",
          "orderSn"=>$order["sn"],
          "category"=>"inflow",
          'name' => '入账',
          "createdTime"=>$order["createdTime"],
        );

        
        $inflow = $this->addFlow($cashFlow);

        $this->num++;
        $number = sprintf("%05d", $this->num);

        $cashFlow = array(
          "userId"=>$order["userId"],
          "sn"=> ($this->time . $number),
          "type"=>"outflow",
          "amount"=>$order["amount"],
          "cashType"=>"RMB",
          'name' => '出账',
          "orderSn"=>$order["sn"],
          "category"=>"outflow",
          "createdTime"=>$order["createdTime"],
          "parentSn" => $inflow["sn"]
        );

        $inflow = $this->addFlow($cashFlow);

        $fields = array("cashSn" => $cashFlow["sn"]);
      }

      if($order) {
        $setting = array(
            'name'  => "order_refund",
            'value' => serialize(array("processId"=>$order["id"]))
        );

        $this->getConnection()->delete("setting", array('name' => "order_refund"));
        $this->addSetting($setting);
      }

    }

    private function proccessCashOrders()
    {
      $sql = "select * from cash_orders where amount > 0 and status ='paid'";
      $orders = $this->getConnection()->fetchAll($sql, array());

      if(empty($orders) || count($orders)==0){
        return ;
      }

      foreach ($orders as $key => $order) {

        $currentTime = date('YmdHis');
        if($this->time != $currentTime){
          $this->time = $currentTime;
          $this->num = 0;
        } else {
          $this->num++;
        }

        $number = sprintf("%05d", $this->num);

        $cashFlow = array(
          "userId"=>$order["userId"],
          "sn"=> ($this->time . $number),
          "type"=>"inflow",
          "amount"=>$order["amount"],
          "cashType"=>"RMB",
          "orderSn"=>$order["sn"],
          "category"=>"inflow",
          'name' => '入账',
          "createdTime"=>$order["createdTime"],
        );

        
        $inflow = $this->addFlow($cashFlow);

        $this->num++;
        $number = sprintf("%05d", $this->num);

        $cashFlow = array(
          "userId"=>$order["userId"],
          "sn"=> ($this->time . $number),
          "type"=>"outflow",
          "amount"=>$order["amount"],
          "cashType"=>"RMB",
          'name' => '出账',
          "orderSn"=>$order["sn"],
          "category"=>"outflow",
          "createdTime"=>$order["createdTime"],
          "parentSn" => $inflow["sn"]
        );

        $inflow = $this->addFlow($cashFlow);

        $fields = array("cashSn" => $cashFlow["sn"]);
      }

      if($order) {
        $setting = array(
            'name'  => "cahs_orders",
            'value' => serialize(array("processId"=>$order["id"]))
        );

        $this->getConnection()->delete("setting", array('name' => "cash_orders"));
        $this->addSetting($setting);
      }

    }

    private function proccess()
    {

      $setting = $this->getSetting("__orders");

      if(isset($setting["value"])){
        $value = $setting["value"];
        if(isset($value["processId"])){
          $processId = $value["processId"];
        }
      } else {
        $processId = 0;
      }

      $sql = "select * from orders where id>".$processId." and payment<>'coin' and amount > 0 and status in ('paid','refunding','refunded') ORDER BY id LIMIT 0,2000";
      $orders = $this->getConnection()->fetchAll($sql, array());

      if(empty($orders) || count($orders)==0){
        return ;
      }

      foreach ($orders as $key => $order) {

        $currentTime = date('YmdHis');
        if($this->time != $currentTime){
          $this->time = $currentTime;
          $this->num = 0;
        } else {
          $this->num++;
        }

        $number = sprintf("%05d", $this->num);

        $cashFlow = array(
          "userId"=>$order["userId"],
          "sn"=> ($this->time . $number),
          "type"=>"inflow",
          "amount"=>$order["amount"],
          "cashType"=>"RMB",
          "orderSn"=>$order["sn"],
          "category"=>"inflow",
          'name' => '入账',
          "createdTime"=>$order["createdTime"],
        );

        
        $inflow = $this->addFlow($cashFlow);

        $this->num++;
        $number = sprintf("%05d", $this->num);

        $cashFlow = array(
          "userId"=>$order["userId"],
          "sn"=> ($this->time . $number),
          "type"=>"outflow",
          "amount"=>$order["amount"],
          "cashType"=>"RMB",
          'name' => '出账',
          "orderSn"=>$order["sn"],
          "category"=>"outflow",
          "createdTime"=>$order["createdTime"],
          "parentSn" => $inflow["sn"]
        );

        $inflow = $this->addFlow($cashFlow);

        $fields = array("cashSn" => $cashFlow["sn"]);
        $this->updateOrder($order["id"], $fields);
      }

      if($order) {
        $setting = array(
            'name'  => "__orders",
            'value' => serialize(array("processId"=>$order["id"]))
        );

        $this->getConnection()->delete("setting", array('name' => "__orders"));
        $this->addSetting($setting);
      }

    }

    private function getSetting($name){
      $sql = "SELECT * FROM setting WHERE name = '".$name."' LIMIT 1";
      $setting = $this->getConnection()->fetchAssoc($sql, array());
      if(!empty($setting)) {
        $setting["value"] = unserialize($setting["value"]);
      }
      return $setting;
    }

    private function updateOrder($id, $fields){
      $this->getConnection()->update("orders", $fields, array('id' => $id));
    }

    private function addFlow($cashFlow){
      $this->getConnection()->insert("cash_flow", $cashFlow);
      $id = $this->getConnection()->lastInsertId();
      $sql = "SELECT * FROM cash_flow WHERE id = ? LIMIT 1";
      $inflow = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
      return $inflow;
    }

    private function addSetting($setting){
      $this->getConnection()->insert("setting", $setting);
      $id = $this->getConnection()->lastInsertId();
      $sql = "SELECT * FROM setting WHERE id = ? LIMIT 1";
      $setting = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
      return $setting;
    }
    
 }


 abstract class AbstractUpdater
 {
    protected $kernel;
    public function __construct ($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
   
 }