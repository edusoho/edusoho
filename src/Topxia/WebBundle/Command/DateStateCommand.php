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

    }



    protected  function computeGuestState($currentDay,$currentTime)
    {
            $gs['date']=$currentDay;

            $gs['totalGuest'] = $this->getCount("select count(*) from guest where createdTime <= ".$currentTime);

            $gs['dayGuestPv'] = $this->getCount("select count(*) from access_log where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600));

            $gs['dayNewGuest'] = $this->getCount("select count(*) from guest where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600));

            $gs['dayActGuest'] = $this->getCount("select count(*) from guest where lastAccessTime <= ".$currentTime." and lastAccessTime >".($currentTime-24*3600));

            $gs['oneWeekActGuest'] = $this->getCount("select count(*) from guest where lastAccessTime <= ".$currentTime." and lastAccessTime >".($currentTime-24*3600*7));

            $gs['oneMonthActGuest'] = $this->getCount("select count(*) from guest where lastAccessTime <= ".$currentTime." and lastAccessTime >".($currentTime-24*3600*30));

            $gs['oneMonthActGuest'] = $this->getCount("select count(*) from guest where lastAccessTime <= ".$currentTime." and lastAccessTime >".($currentTime-24*3600*30));

            $gs['oneMonthLoseGuest'] = $this->getCount("select count(*) from guest where lastAccessTime <= ".$currentTime." and lastAccessTime <".($currentTime-24*3600*30));

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

            $gs['dayUserPv'] = $this->getCount("select count(*) from access_log where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600)." and userId > 0 " );

            $gs['dayRegUser'] = $this->getCount("select count(*) from user where createdTime <= ".$currentTime." and createdTime >".($currentTime-24*3600)."  " );

             $gs['dayActUser'] = $this->getCount("select count(*) from user where loginTime <= ".$currentTime." and loginTime >".($currentTime-24*3600)."  " );

             $gs['oneWeekActUser'] = $this->getCount("select count(*) from user where loginTime <= ".$currentTime." and loginTime >".($currentTime-24*3600*7)."  " );

             $gs['oneMonthActUser'] = $this->getCount("select count(*) from user where loginTime <= ".$currentTime." and loginTime >".($currentTime-24*3600*30)."  " );

           

            $this->getUserStateService()->deleteByDate($currentDay);

            $this->getUserStateService()->createUserState($us);
    }


    protected function getCount($sql){

        $connection = $this->getContainer()->get('database_connection');

        return  $connection->fetchColumn($sql);       

           
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