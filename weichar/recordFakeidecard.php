<?php
include("loginwp.php");
$from = $_POST["from"];
$ctime=$_POST["time"];
$test = new __user_remote_opera( );
$test->keepLive();
$mysql = new SaeMysql();	
$msg=$test->getMsg();
preg_match_all('/(\{.*\})/isU',$msg,$res);
foreach($res[1] as $m)
{
    $json=json_decode($m,1);
    if($json["date_time"] == $ctime)
    {
        $fakeid = $json["fakeid"];
        echo $fakeid;
        $sql = "update ecard set fakeid='" . $fakeid . "' where openid='" . $from . "'";
        $mysql->runSql($sql);
        break;
    }
}
$mysql->closeDb();
?>