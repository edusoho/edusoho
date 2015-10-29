<?php

namespace Topxia\Common\Tests;

use Topxia\Service\Common\BaseTestCase;

class NumberToolkitTest extends BaseTestCase
{

        public function testroundUp(){

              $testNumArray=array(123.456,783463.437843,12.01,34.00,45.5,8934.00001,100.101,34.10,33.30,40);
              $testPrecision=2;
              $testString="";
              for($i=0;$i<8;$i++){
              	  $testTem=0;
                       $testAmt = explode(".",$testNumArray[$i]);
                       if(count($testAmt)==0){
                       	return 0;
                       }

                       if(count($testAmt)==1){
                       	return $testAmt[0];
                       }

                       if(strlen($testAmt[1]) > $testPrecision){
                       	$testFloatStr = substr($testAmt[1],$testPrecision);
                       	$testAmt[1] = (float)(".".substr($testAmt[1],0,$testPrecision));

                       	if((int)substr($testFloatStr,0,$testPrecision+2)>0){
                       		$testNext = (int)$testFloatStr;
                       		if($testNext != 0){
                                                $testrUp="";
                                          for($x=1;$x<$testPrecision;$x++){
                                          	$testrUp .= "0";
                                          }
                                          $testAmt[1] = $testAmt[1] + (float)(".".$testrUp."1");
                       		}
                       	}
                       }
                       else{
                       	$testAmt[1] = (float)(".".$testAmt[1]);
                       }
                   $testTem = $testAmt[0]+$testAmt[1];
                   $testString="  ".$testTem;
              }

              preg_match_all('^\d+(\.\d{2})?$',$testString,$matchs);

              $this->assertEquals(10,count($matchs));

        }

}