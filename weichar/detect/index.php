<?php

include ( 'detect.php' );
echo "<img src='http://liyang-new.stor.sinaapp.com/20102212231.jpg' /><br>";
$stor = new SaeStorage();
$valid = new Valite();
$temp_dir = 'http://liyang-new.stor.sinaapp.com/20102212231.jpg';
$valid->setImage($temp_dir);
$valid->getHec();
$validCode = $valid->run();
echo $validCode;
//$valid->getCode();
?>