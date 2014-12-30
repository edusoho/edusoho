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

          $this->getConnection()->commit();
      } catch(\Exception $e) {
          $this->getConnection()->rollback();
          throw $e;
      }
    }

    private function proccess()
    {
      $this->createDao('Cash.CashFlowDao')->getConnection()->beginTransaction();
      try{
      $sql = "select * from orders where amount > 0 and id >60000 and status ='paid'";
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
        $inflow=$this->createDao('Cash.CashFlowDao')->addFlow($cashFlow);

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
        $this->createDao('Cash.CashFlowDao')->addFlow($cashFlow);

        $fields = array("cashSn" => $cashFlow["sn"]);
        $this->createDao('Order.OrderDao')->updateOrder($order["id"], $fields);

        }

        $this->createDao('Cash.CashFlowDao')->getConnection()->commit();

      }catch(\Exception $e){

            $this->createDao('Cash.CashFlowDao')->getConnection()->rollback();
            throw $e;
      }
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