<?php
include "ecard.php";
include "loginwp.php";
$openid = $_POST['openid'];
$mysql = new SaeMysql();
$result = $mysql->getData("SELECT * FROM `ecard` where openid='" . $openid . "'");
$newecard = new ecard( $result[0]['mima'], $result[0]['xuehao'], $result[0]['openid'] );
$check = $newecard->login();
if ( $check == 2 ) {
    echo 'LoginOk';
    $newecard->Getconsumption();
}
if ( $result[0]['send'] == 1 && ! empty( $result[0]['fakeid'] ) ) {
    echo 'Sending';
    $test = new __user_remote_opera();
    $test->keepLive();
    $end = $test->send($result[0]['fakeid'], $result[0]['xiaofei_now']);
    $json = json_decode($end, 1);
    if ( $json['base_resp']['err_msg'] == 'ok' ) {
        $sql = "update ecard set send = 0 where openid='" . $openid . "'";
        $mysql->runSql($sql);
        echo 'SendOk';
    }
}
else {
    echo 'NoNewMsg';
}
?>