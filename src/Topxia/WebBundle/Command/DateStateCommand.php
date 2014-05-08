<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class DateStateCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName ( 'topxia:date-state' );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $currentDay = date('Y-m-d', time()-24*3600);

        $currentTime = strtotime($currentDay .' 23:59:59');

        $this->initServiceKernel();

        $this->computeGuestState($currentDay,$currentTime);

        $this->computeUserState($currentDay,$currentTime);

        $this->computePartnerState($currentDay,$currentTime);

        $this->computeBusinessState($currentDay,$currentTime);

        $this->computeBusinessStateByProdType($currentDay,$currentTime,'course');

        $this->computeBusinessStateByProdType($currentDay,$currentTime,'activity');

        $this->bakAccessLog($currentDay,$currentTime);

    }



    protected  function computeGuestState($currentDay,$currentTime)
    {
            $gs['date']=$currentDay;

            $gs['totalGuest'] = $this->getCount("select count(*) from guest where createdTime <= ".$currentTime);

            $gs['dayGuestPv'] = $this->getCount("select count(*) from access_log where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600));

            $gs['dayNewGuest'] = $this->getCount("select count(*) from guest where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600));

            $gs['dayActGuest'] = $this->getCount("select count(distinct guestId)  from access_log where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600));

            $gs['oneWeekActGuest'] = $this->getCount("select count(distinct guestId) from access_log where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600*7));

            $gs['oneMonthActGuest'] = $this->getCount("select count(distinct guestId)  from access_log where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600*30));

            $gs['oneMonthLoseGuest'] = $this->getCount("select count(*) from guest where lastAccessTime <= ".($currentTime-24*3600*30));

            $gs['twoMonthLoseGuest'] = $this->getCount("select count(*) from guest where  lastAccessTime <=".($currentTime-24*3600*30*2));

            $gs['threeMonthLoseGuest'] = $this->getCount("select count(*) from guest where  lastAccessTime <=".($currentTime-24*3600*30*3));

            $gs['sixMonthLoseGuest'] = $this->getCount("select count(*) from guest where  lastAccessTime <=".($currentTime-24*3600*30*6));

            $gs['oneMonthAgoLoseNewGuest'] = $this->getCount("select count(*) from guest where  lastAccessTime <=".($currentTime-24*3600*30)." and createdTime >".($currentTime-24*3600*31)." and createdTime < ".($currentTime-24*3600*30));

            $gs['oneMonthAgoRegGuest'] = $this->getCount("select count(*) from guest where createdTime >".($currentTime-24*3600*31)." and createdTime < ".($currentTime-24*3600*30));

            $gs['twoMonthAgoLoseNewGuest'] = $this->getCount("select count(*) from guest where  lastAccessTime <=".($currentTime-24*3600*60)." and createdTime >".($currentTime-24*3600*61)." and createdTime < ".($currentTime-24*3600*60));

            $gs['twoMonthAgoRegGuest'] = $this->getCount("select count(*) from guest where createdTime >".($currentTime-24*3600*61)." and createdTime < ".($currentTime-24*3600*60));

            $gs['oneMonthAgoLoseNewGuest'] = $this->getCount("select count(*) from guest where  lastAccessTime <=".($currentTime-24*3600*90)." and createdTime >".($currentTime-24*3600*91)." and createdTime < ".($currentTime-24*3600*90));

            $gs['oneMonthAgoRegGuest'] = $this->getCount("select count(*) from guest where createdTime >".($currentTime-24*3600*91)." and createdTime < ".($currentTime-24*3600*90));

            $gs['oneMonthAgoLoseNewGuest'] = $this->getCount("select count(*) from guest where  lastAccessTime <=".($currentTime-24*3600*120)." and createdTime >".($currentTime-24*3600*121)." and createdTime < ".($currentTime-24*3600*120));

            $gs['oneMonthAgoRegGuest'] = $this->getCount("select count(*) from guest where createdTime >".($currentTime-24*3600*121)." and createdTime < ".($currentTime-24*3600*120));

            $this->getGuestStateService()->deleteByDate($currentDay);

            $this->getGuestStateService()->createGuestState($gs);
    }

    protected  function computeUserState($currentDay,$currentTime)
    {
            $us['date']=$currentDay;

            $us['totalUser'] = $this->getCount("select count(*) from user where createdTime <= ".$currentTime);

            $us['totalVerifyUser'] = $this->getCount("select count(*) from user where createdTime <= ".$currentTime." and emailVerified=1");

            $us['dayUserPv'] = $this->getCount("select count(*) from access_log where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600)." and userId > 0 " );

            $us['dayRegUser'] = $this->getCount("select count(*) from user where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600)."" );

            $us['dayActUser'] = $this->getCount("select count(distinct userId)  from access_log where userId>0 and   createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600)."" );

            $us['oneWeekActUser'] = $this->getCount("select count(distinct userId) from access_log where userId>0 and createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600*7)."  " );

            $us['oneMonthActUser'] = $this->getCount("select count(distinct userId) from access_log where userId>0 and  createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600*30)."" );


            $us['oneMonthLoseUser'] = $this->getCount("select count(*) from user where  loginTime <= ".($currentTime-24*3600*30)."" );

            $us['twoMonthLoseUser'] = $this->getCount("select count(*) from user where  loginTime <= ".($currentTime-24*3600*30*2)."" );

            $us['threeMonthLoseUser'] = $this->getCount("select count(*) from user where  loginTime <= ".($currentTime-24*3600*30*3)."" );

            $us['sixMonthLoseUser'] = $this->getCount("select count(*) from user where  loginTime <= ".($currentTime-24*3600*30*6)."" );

            $us['oneMonthAgoLoseNewUser'] = $this->getCount("select count(*) from user where  loginTime <= ".($currentTime-24*3600*30)." and createdTime > ".($currentTime-24*3600*31)." and createdTime < ".($currentTime-24*3600*30)."" );

            $us['oneMonthAgoRegUser'] = $this->getCount("select count(*) from user where createdTime > ".($currentTime-24*3600*31)." and createdTime < ".($currentTime-24*3600*30)."" );

            $us['twoMonthAgoLoseNewUser'] = $this->getCount("select count(*) from user where  loginTime <= ".($currentTime-24*3600*30*2)." and createdTime > ".($currentTime-24*3600*61)." and createdTime < ".($currentTime-24*3600*30*2)."" );

            $us['twoMonthAgoRegUser'] = $this->getCount("select count(*) from user where createdTime > ".($currentTime-24*3600*61)." and createdTime < ".($currentTime-24*3600*60)."" );

            $us['oneMonthAgoLoseNewUser'] = $this->getCount("select count(*) from user where  loginTime <= ".($currentTime-24*3600*90)." and createdTime > ".($currentTime-24*3600*91)." and createdTime < ".($currentTime-24*3600*90)."" );

            $us['oneMonthAgoRegUser'] = $this->getCount("select count(*) from user where createdTime > ".($currentTime-24*3600*91)." and createdTime < ".($currentTime-24*3600*90)."" );

            $us['oneMonthAgoLoseNewUser'] = $this->getCount("select count(*) from user where  loginTime <= ".($currentTime-24*3600*120)." and createdTime > ".($currentTime-24*3600*121)." and createdTime < ".($currentTime-24*3600*120)."" );

            $us['oneMonthAgoRegUser'] = $this->getCount("select count(*) from user where createdTime > ".($currentTime-24*3600*121)." and createdTime < ".($currentTime-24*3600*120)."" );

           
            $this->getUserStateService()->deleteByDate($currentDay);

            $this->getUserStateService()->createUserState($us);
    }

    protected  function computePartnerState($currentDay,$currentTime)
    {
            $ps['date']=$currentDay;

            $partners = $this->getPartners($currentTime);

            foreach ($partners as $partner) {

                $partnerId = $partner['partnerId'];

                if($partnerId == 0 ){
                    continue;
                }
                            

                $ps['partnerId'] = $partnerId;

                $ps['courseOrders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and targetType='course' and id in (select orderId from sale_commission where salerId = ".$partnerId." )");

                $ps['courseOrdersFee'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and amount > 0 and targetType='course' and id in (select orderId from sale_commission where salerId = ".$partnerId." )");

                $ps['courseOrdersFree'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and amount = 0  and targetType='course' and id in (select orderId from sale_commission where salerId = ".$partnerId." )");

                $ps['activityOrders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid'  and targetType='activity' and id in (select orderId from sale_commission where salerId = ".$partnerId." )");

                $ps['vipOrders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid'  and targetType='vip' and id in (select orderId from sale_commission where salerId = ".$partnerId." )");

                $ps['dayActGuest'] = $this->getCount("select count(distinct guestId) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and partnerId= ".$partnerId."  ");

                $ps['dayNewGuest'] = $this->getCount("select count(*) from guest where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and createdPartnerId= ".$partnerId."  ");

                $ps['dayActUser'] = $this->getCount("select count(distinct userId) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and userId>0 and  partnerId= ".$partnerId."  ");

                $ps['dayRegUser'] = $this->getCount("select count(*) from user where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and id in (select userId from access_log where partnerId = ".$partnerId.")  ");

                $ps['dayPv'] = $this->getCount("select count(*) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and partnerId = ".$partnerId." ");

                $this->getPartnerStateService()->deleteByDate($currentDay,$partnerId);

                $this->getPartnerStateService()->createPartnerState($ps);

            }

    }


    protected  function computeBusinessState($currentDay,$currentTime)
    {
            $bs['date']=$currentDay;

            $prods = $this->getProds($currentTime);

            foreach ($prods as $prod) {

                $prodType= $prod['prodType'];
               
                $prodId = $prod['prodId'];                            

                $bs['prodType'] = $prodType;
               
                $bs['prodId'] = $prodId;

                $bs['orders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and prodType='".$prodType."' and prodId = ".$prodId."");             

                $bs['feeOrders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and amount>0 and prodType='".$prodType."' and prodId =".$prodId."");

                $bs['freeOrders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and amount=0 and prodType='".$prodType."' and prodId = ".$prodId."");

                if($prodType == 'course'){

                    $bs['dayGuestVisit'] = $this->getCount("select count(*) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and accessPathName = '/course/".$prodId."' ");

                    $bs['dayGuest'] = $this->getCount("select count(distinct guestId) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and accessPathName = '/course/".$prodId."' ");

                    $course = $this->getCourseService()->getCourse($prodId);

                    $bs['prodName'] = $course['title'];

                }else if ($prodType == 'activity'){

                    $bs['dayGuestVisit'] = $this->getCount("select count(*) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and accessPathName = '/openclass/".$prodId."/show' ");

                    $bs['dayGuest'] = $this->getCount("select count(distinct guestId) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and accessPathName = '/openclass/".$prodId."/show' ");

                    $activity = $this->geActivityService()->getActivity($prodId);

                    $bs['prodName'] = $activity['title'];


                }

                $this->getBusinessStateService()->deleteByDate($currentDay,$prodType,$prodId);

                $this->getBusinessStateService()->createBusinessState($bs);

            }

    }

    protected  function computeBusinessStateByProdType($currentDay,$currentTime,$prodType)
    {

                $bs['date']=$currentDay;

                               

                $bs['prodType'] = $prodType;
               
                $bs['prodId'] = 0;

                $bs['orders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and prodType='".$prodType."' ");           

                $bs['feeOrders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and amount>0 and prodType='".$prodType."' ");

                $bs['freeOrders'] = $this->getCount("select count(*) from orders where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and status='paid' and amount=0 and prodType='".$prodType."' ");

                if($prodType == 'course'){

                    $bs['dayGuestVisit'] = $this->getCount("select count(*) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and accessPathName REGEXP '^(/course/)([[:digit:]]+)$' ");

                    $bs['dayGuest'] = $this->getCount("select count(distinct guestId) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and accessPathName REGEXP '^(/course/)([[:digit:]]+)$' ");

                   
                    $bs['prodName'] = "所有课程";

                }else if ($prodType == 'activity'){

                    $bs['dayGuestVisit'] = $this->getCount("select count(*) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and accessPathName REGEXP '^(/openclass/)([[:digit:]]+)(/show)$' ");

                    $bs['dayGuest'] = $this->getCount("select count(distinct guestId) from access_log where createdTime < ".$currentTime." and  createdTime > ".($currentTime-24*3600)." and accessPathName REGEXP  '^(/openclass/)([[:digit:]]+)(/show)$' ");

                    $bs['prodName'] = "所有活动";


                }

                $this->getBusinessStateService()->deleteByDate($currentDay,$prodType,0);

                $this->getBusinessStateService()->createBusinessState($bs);

           


    }


     protected  function bakAccessLog($currentDay,$currentTime){


         $connection = $this->getContainer()->get('database_connection');

        

         $connection->executeUpdate("insert into access_log_history (logId,guestId,userId,prodType,prodName,prodId,accessHref,accessPathName,accessSearch,createdIp,createdTime,mTookeen,partnerId) select id,guestId,userId,prodType,prodName,prodId,accessHref,accessPathName,accessSearch,createdIp,createdTime,mTookeen,partnerId from access_log b where b.createdTime < ".($currentTime-3*24*3600));




          $connection->executeUpdate("delete from access_log  where  createdTime < ".($currentTime-3*24*3600));


    }





    protected  function getPartners($currentTime)
    {

        return  $this->getRs("select distinct partnerId as partnerId from access_log where createdTime< ".$currentTime." and createdTime > ".($currentTime-24*3600));

    }


    protected  function getProds($currentTime)
    {

        return  $this->getRs("select distinct prodType as prodType, prodId as prodId  from orders where createdTime< ".$currentTime." and createdTime > ".($currentTime-24*3600));

    }


    protected function getCount($sql){

        $connection = $this->getContainer()->get('database_connection');

        return  $connection->fetchColumn($sql);    
    }

    protected function getRs($sql){

        $connection = $this->getContainer()->get('database_connection');

        return $connection->fetchAll($sql);    

           
    }

    protected function getGuestStateService()
    {
        return $this->getServiceKernel()->createService('State.GuestStateService');
    }

    protected function getUserStateService()
    {
        return $this->getServiceKernel()->createService('State.UserStateService');
    }

    protected function getPartnerStateService()
    {
        return $this->getServiceKernel()->createService('State.PartnerStateService');
    }

    protected function getBusinessStateService()
    {
        return $this->getServiceKernel()->createService('State.BusinessStateService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function geActivityService()
    {
        return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

    private function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setConnection($this->getContainer()->get('database_connection'));
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' =>  '127.0.0.1',
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }




}