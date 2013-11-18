<?php
namespace Topxia\Common;

class DebugToolkit
{

	public static  function print_stack_trace()
	{
	   $array =debug_backtrace();
   
	   $html="";
	   foreach($array as $row)
	   {
	   	   $html .= '文件：';
	   	   if(!empty($row['file']))
	   	   {
	   	   	  $html .= ' '.$row['file'].' ';
	   	   }
	   	   $html .= '行：';
	   	   if(!empty($row['line']))
	   	   {
	   	   	  $html .= ' '.$row['line'].' ';
	   	   }

	   	   $html .= '方法：';
	   	   if(!empty($row['function']))
	   	   {
	   	   	  $html .=' '.$row['function']."\n";
	   	   }
	      
	   }
	   
	   return $html;
	}
}