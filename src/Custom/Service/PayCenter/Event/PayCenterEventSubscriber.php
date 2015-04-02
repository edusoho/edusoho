<?php
namespace Custom\Service\PayCenter\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;

class PayCenterEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'order.pay.success' => array('giveCoin', 0),
        );
    }

    public function giveCoin(ServiceEvent $event)
    {
        $content = $event->getSubject();
        $order = $content['order'];
        $changeAmount = $order['amount'];
        $change=$this->getCashAccountService()->getChangeByUserId($order['userId']);

        if(empty($change)){

            $change=$this->getCashAccountService()->addChange($order['userId']);
        }

        list($canUseAmount,$canChange,$data)=$this->caculate($changeAmount,0,array());
        if($canChange>0){
            $this->getCashAccountService()->changeCoin($changeAmount-$canUseAmount,$canChange,$order['userId']);
        }
    }
    private function caculate($amount,$canChange,$data)
        {
        $coinSetting= $this->getSettingService()->get('coin',array());

        $coinRanges=$coinSetting['coin_consume_range_and_present'];

        if($coinRanges==array(array(0,0))) return array($amount,$canChange,$data);

        array_multisort($coinRanges, SORT_ASC );
        $sendAmount = 0 ;
        for( $i = 0 ; $i < count ( $coinRanges ); $i++){
            if ( $amount >= $coinRanges[$i][0] ) {
                $sendAmount += $coinRanges[$i][1];
            }
        }
        
        if($sendAmount > 0){
            $data[]=array(
            'send'=>"消费满{{$amount}}元送{{$sendAmount}}",
            'sendAmount'=>"{{$sendAmount}}",);
        }

       return array(0,$sendAmount,$data);

    }

    protected function getCashAccountService()
    {
        return serviceKernel::instance()->createService('Cash.CashAccountService');
    }
    protected function getSettingService()
    {
        return serviceKernel::instance()->createService('System.SettingService');
    }
}