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
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.0/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <div class="container">
        <form class="form-signin" role="form" action="/ecard.php" id="new_user" method="post">
            <h4 class="form-signin-heading"><font color= 339966>鲁东大学校园一卡通绑定</font></h4>
        <input type="text" class="form-control" name="xuehao" placeholder="请输入你的学号" required autofocus>
        <input type="password" class="form-control" name="mima" placeholder="请输入一卡通密码" required autofocus>
                  <input type="hidden" value="<?php echo $_REQUEST['openid']?>" name="openid" >

        <button class="btn btn-lg btn-success btn-block" type="submit">提交</button>
      </form>
      
      
      
<?php
include "weichar/ecard.php";
$openid=$_REQUEST['openid'];
$xuehao=$_REQUEST['xuehao'];
$mima=$_REQUEST['mima'];
  if(!empty($xuehao)&&empty($mima))
  {
      
      ?>
<div class="row">

            <div class="col-md-4"></div>
 			 <div class="col-md-4"> 
                 <h4>
                     <div class="alert alert-danger alert-dismissable">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  <strong>请输入您的密码！默认为身份证后六位，如有x，则不计入x.</strong> 
                     
                  	</div>
                     </h4>
			 </div>
  			<div class="col-md-4"></div>   
      </div>
      <?php
      
  }
else if(!empty($xuehao)&&!empty($mima))
{
        $mysql = new SaeMysql();
		if(strlen($xuehao)==11)
    	{
      		$result = $mysql->getData("SELECT * FROM `ecard` where openid='".$openid."'");
            if(empty($result))
            {
                $sql = "INSERT  INTO `ecard` (  `openid` )  VALUES (  '" . $openid . "' ) ";
                $mysql->runSql($sql);
            }
      		if($result[0]['valid']==1)
      		{
          		?>
            <div class="row">

            <div class="col-md-4"></div>
 			 <div class="col-md-4"> 
                 <h1><div class="alert alert-info"><h4>您已经绑定过，无需再次绑定，如需更换，请返回微信，先输入:解绑，然后输入：ecard</h4></div>
			</h1>
			 </div>
  			<div class="col-md-4"></div>   
      </div>
          	<?php    
   
            }//if
    		else
    		{
                $newecard = new ecard($mima,$xuehao,$openid);
				$check = $newecard->login();
                if($check == 0)
                {
                    $error="由于一卡通网络无法访问，绑定失败，请稍后再试.";
                }
                else if ($check == 1)
                {
                    $error="密码或用户名有误，请重新输入.";
                }
                else if($check == 2)
                {
                    $error="绑定成功，请返回微信输入：ecard";
    				$newecard->Getconsumption();                   
                }
                else
                {
                    $error='未知错误';
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
            }//else           
        }
    	else
        {
            ?>
              <div class="row">

            <div class="col-md-4"></div>
 			 <div class="col-md-4"> 
                 <h4>
                     <div class="alert alert-info">
                         <strong>请输入正确的学号！</strong> 
                                      	</div>
                     </h4>
			 </div>
  			<div class="col-md-4"></div>   
      </div>
          	<?php  
      
        }
}
else
{
}
?>
          
          <div class="row">
  <div class="col-md-4"></div>

             <div class="col-md-4"> <button type="button" class="btn btn-info">温馨提示</button>
        
                 <p><font color=#6699FF><strong>校园一卡通绑定说明：<br>每位同学仅需绑定一次，如需解绑，请在微信平台输入:解绑<br>密码默认为身份证号码后六位，如果身份证最后一位是X，则不计入X。<br>如反复绑定均未成功，或者其他问题，可联系QQ:287578574.</strong>
      </font>
</p>
</div>
  <div class="col-md-4"></div>
</div>
       

    </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
  </body>
</html>
