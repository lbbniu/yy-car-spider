<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-2-4
 * Time: 下午7:04
 */
header("Content-Type:application/javascript;charset=UTF-8");
date_default_timezone_set('Asia/Chongqing');
set_time_limit(0);
$C = new stdClass();
$C->webpath = dirname(__file__) . '/';
require_once 'config/conf_main.php';
require_once 'includes/class_mysql.php';
require_once 'includes/func_main.php';

$dbm = new mysql($C->DB_HOST_M, $C->DB_USER, $C->DB_PASS, $C->DB_NAME);
$str = file_get_contents("http://www.yy.com/index/t?tabId=101001&subTabId=&offset=0&limit=1000&async=true");
$json = json_decode($str,true);
//print_r($json);
$time = time();
if(isset($json['data']['lives'])){
   $count = count($json['data']['lives']);
	//echo $count;
   $res = $json['data']['lives'];
   $sql = "INSERT INTO yy(sid, liveUid, users, channelName, liveName, leafType, liveTime, frontTag, type, startTime, thumb,create_time) VALUES";
   $sqlTemp = array();
   for($i=0;$i<$count;$i++){
		$data = $res[$i];
		foreach($data as $key=>$val){
			$data[$key] = is_array($val)?$val:$dbm->e($val);;
		}
		$sqlTemp[]= "('{$data['sid']}','{$data['liveUid']}','{$data['users']}','{$data['channelName']}','{$data['liveName']}','{$data['leafType']}','{$data['liveTime']}','{$data['frontTag']}','{$data['type']}','{$data['startTime']}','{$data['thumb']}','$time')";
		//echo $i."\n";
   }
   $sql .= implode(",\n",$sqlTemp);
   //echo $sql;
   $dbm->query($sql);
}
echo date("Y-m-d H:i:s",$time)."\r\n";