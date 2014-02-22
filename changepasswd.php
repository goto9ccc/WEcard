<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../docs-assets/ico/favicon.png">

    <title>鲁东大学校园一卡通绑定</title>

    <!-- Bootstrap core CSS -->
    <link href="../dist/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../dist/css/signin.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]>
    <script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="http://cdn.bootcss.com/html5shiv/3.7.0/html5shiv.min.js"></script>
    <script src="http://cdn.bootcss.com/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<div class="container">
    <form class="form-signin" role="form" action="/changepasswd.php" id="new_user" method="post">
        <h4 class="form-signin-heading"><font color=339966>一卡通密码修改</font></h4>
        <input type="password" class="form-control" name="mima1" placeholder="请输入新的一卡通密码" required autofocus>
        <input type="password" class="form-control" name="mima2" placeholder="请再次输入新的密码" required autofocus>
        <input type="hidden" value="<?php echo $_REQUEST['openid'] ?>" name="openid">

        <button class="btn btn-lg btn-success btn-block" type="submit">提交</button>
    </form>



    <?php
    include "weichar/ecard.php";
    $openid = $_REQUEST['openid'];
    $mima1 = $_REQUEST['mima1'];
    $mima2 = $_REQUEST['mima2'];
    function ismima($str)
    {
        return preg_match("/^\d{6}$/", $str);
    }
    $flag1 = ismima($mima1);
    $flag2 = ismima($mima2);

    if ( empty( $mima1 ) ) {
    }
    else if ( empty( $flag1 ) || empty( $flag2 ) || $mima1 !== $mima2 ) {

        ?>
        <div class="row">

            <div class="col-md-4"></div>
            <div class="col-md-4">
                <h4>
                    <div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>请输入您的新密码，必须是6位数字，且两次输入的新密码必须相同.</strong>

                    </div>
                </h4>
            </div>
            <div class="col-md-4"></div>
        </div>
    <?php

    }
    else {
        $mysql = new SaeMysql();
        $result = $mysql->getData("SELECT * FROM `ecard` where openid='" . $openid . "'");
        if ( empty( $result[0]['mima'] ) ) {
            $error = '您还没有绑定一卡通，无法修改！请返回微信输入：ecard 进行绑定.';
        }
        else if ( $result[0]['valid'] == 0 ) {
            $error = '抱歉，一卡通现在未能正常登陆，可能的原因是一卡通官网出现问题，请稍后重试，如果 http://www.ecard.ldu.edu.cn 可以访问，但是问题仍在，请联系我的QQ:287578574.';
        }
        else {
            $newecard = new ecard( $result[0]['mima'], $result[0]['xuehao'], $result[0]['openid'] );
            $check = $newecard->login();
            if ( $check == 0 ) {
                $error = "由于一卡通网络无法访问，修改失败，请稍后再试.";
            }
            else if ( $check == 1 ) {
                $error = "密码或用户名有误，请重新输入.";
            }
            else if ( $check == 2 ) {
                $error = $newecard->ChangePass($mima1);
                $error = mb_convert_encoding($error, "UTF-8", "GBK");
            }
            else {
                $error = '未知错误';
            }
        }
        ?>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <h4>
                    <div class="alert alert-info"><?php echo $error;?></div>
                </h4>
            </div>
            <div class="col-md-4"></div>
        </div>
    <?php
    }
    ?>

    <div class="row">
        <div class="col-md-4"></div>

        <div class="col-md-4">
            <button type="button" class="btn btn-info">温馨提示</button>

            <p><font color="red"><strong>校园一卡通修改密码说明：<br>新密码必须是6位数字，并且请输入新密码两次，以确保无误<br>如有问题，可联系QQ:287578574.</strong>
                </font>
            </p>
        </div>
        <div class="col-md-4"></div>
    </div>


</div>
<!-- /container -->
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
</body>
</html>
