<?php
/**
 * User: Edusoho V8
 * Date: 19/10/2016
 * Time: 18:45
 */

$dates = array();

$activeUsers = array();
for ($i =0; $i<=(6 + 30); $i++){
    $date = date('Y-m-d', time()-$i*24*3600);
    array_push($dates, $date);
    array_push($activeUsers, array('userId'=>$i+rand(1,10),'date'=>$date));
}

var_dump($dates);

var_dump($activeUsers);


$a = array('2016/09/27'=>array(0=>null,1=>null, 2=>null));

$ab = array_unique($a['2016/09/27']);
  var_dump($ab);