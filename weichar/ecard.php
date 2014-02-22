<?php
include_once( "snoopy.class.php" );
include ( 'detect/detect.php' );

class ecard
{
    private $cookie;
    private $_xuehao;
    private $_mm;
    private $_account;
    private $_name;
    private $_yue;
    private $_openid;
    public $error;

    public function __construct($mm, $xuehao, $openid)
    {
        $this->_mm = $mm;
        $this->_xuehao = $xuehao;
        $this->_openid = $openid;
    }

    public function login()
    {
        $mysql = new SaeMysql();
        $result = $mysql->getData("SELECT * FROM `ecard` where openid='" . $this->_openid . "'");
        //验证一下数据库里的cookie是否还有效，若有效则直接获取数据库里的数据赋给类成员
        if ( ! empty( $result ) && $this->checkCookie($result[0]['cookie']) ) {
            $sql = "update `ecard` set valid=1 where openid='" . $this->_openid . "'";
            $mysql->runSql($sql);
            $this->_account = $result[0]['zhanghu'];
            $this->_yue = $result[0]['yue'];
            $this->_name = $result[0]['name'];
            $this->cookie = $result[0]['cookie'];
            return 2;
        }
        $this->getFirstCookie();
        if ( empty( $this->cookie ) ) {
            //echo '获取cookie出错';
            return 0;
        }
        else {
            //登录
            $this->setCapcha();
            $this->saveMM();
            $final_mm = $this->convertMM();
            $url = "http://202.194.48.115/loginstudent.action";
            $send_snoopy = new Snoopy;
            $send_snoopy->rawheaders['Cookie'] = $this->cookie;
            $post = array('name' => $this->_xuehao, 'userType' => '1', 'passwd' => $final_mm, 'loginType' => '2', 'rand' => '2272', 'imageField.x' => '0', 'imageField.y' => '0');
            $send_snoopy->agent = "(Mozilla/5.0 (Windows NT 5.1; rv:19.0) Gecko/20100101 Firefox/19.0)"; //伪装浏览器
            $send_snoopy->submit($url, $post);
            if ( empty( $send_snoopy->results ) ) {
                $sql = "update `ecard` set valid=0";
                $mysql->runSql($sql);
                return 1;
            }
            $this->error .= $send_snoopy->status;
            $send_snoopy = new Snoopy;
            $send_snoopy->rawheaders['Cookie'] = $this->cookie;
            $url = "http://202.194.48.115/accountcardUser.action";
            $send_snoopy->fetch($url);
            $res = $send_snoopy->results;
            if ( empty( $res ) ) {
                $sql = "update `ecard` set valid=0";
                $mysql->runSql($sql);
                return 1;
            }
            //得到用户的姓名及账户号码和余额
            preg_match_all('|<div align="left">(.*)</div>|iU', $res, $m);
            $this->_name = $m[1][0];
            $this->_account = $m[1][1];
            preg_match_all('|<td class="neiwen">(.*)元.*</td>|iU', $res, $n);
            $this->_yue = $n[1][0];
            //跟新数据库内容，
            if ( empty( $m ) ) {
                $sql = "update `ecard` set valid=0";
                $mysql->runSql($sql);
                return 1;
            }
            else {
                $sql = "update `ecard` set valid=1,xuehao='" . $this->_xuehao . "',mima='" . $this->_mm . "',name='" . $this->_name . "',yue='" . $this->_yue . "',zhanghu='" . $this->_account . "',cookie='" . $this->cookie . "' where openid='" . $this->_openid . "'";
                $mysql->runSql($sql);
                return 2;
            }
        }
    }

    public function getFirstCookie()
    {
        //初始访问网页，获得cookie
        $url = "http://202.194.48.115/homeLogin.action";
        $snoopy = new Snoopy;
        $snoopy->fetch($url);
        $cookie = trim($snoopy->headers[4]);
        $cookie = str_replace("Set-Cookie: ", "", $cookie);
        $cookie = str_replace("; Path=/", "", $cookie);
        $this->cookie = $cookie;
    }

    public function setCapcha()
    {
        //设置验证码为指定数字
        $snoopy = new Snoopy;
        $snoopy->rawheaders['Cookie'] = $this->cookie;
        $url = "http://202.194.48.115/getCheckpic.action?rand=2272.198183298111";
        $snoopy->fetch($url);
        $capcha = $snoopy->results;
    }

    public function checkCookie($cookie)
    {
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $cookie;
        $url = "http://202.194.48.115/accountcardUser.action";
        $send_snoopy->fetch($url);
        $res = $send_snoopy->results;
        if ( empty( $res ) ) {
            return 0;
        }
        return 1;
    }

    public function saveMM()
    {
        //获取密码图片并储存为:学号.jpg
        $snoopy = new Snoopy;
        $snoopy->rawheaders['Cookie'] = $this->cookie;
        $url = "http://202.194.48.115/getpasswdPhoto.action";
        $snoopy->fetch($url);
        $mima = $snoopy->results;
        $stor = new SaeStorage();
        $img = new SaeImage();
        $img->setData($mima);
        $res = $img->exec('jpg');
        $stor->write('new', $this->_xuehao . '.jpg', $res);
    }

    public function convertMM()
    {
        //识别密码图，并转换密码
        $valid = new Valite();
        $stor = new SaeStorage();
        $temp_dir = 'http://liyang-new.stor.sinaapp.com/' . $this->_xuehao . '.jpg';
        $valid->setImage($temp_dir);
        $valid->getHec();
        $validCode = $valid->run();
        $toconvert = $validCode;
        for ($i = 0; $i < strlen($this->_mm); $i ++) {
            $final_mima .= strpos($toconvert, $this->_mm[$i]);
        }
        return $final_mima;
    }

    public function convertNewMM($mima)
    {
        //识别密码图，并转换密码
        $valid = new Valite();
        $stor = new SaeStorage();
        $temp_dir = 'http://liyang-new.stor.sinaapp.com/' . $this->_xuehao . '.jpg';
        $valid->setImage($temp_dir);
        $valid->getHec();
        $validCode = $valid->run();
        $toconvert = $validCode;
        for ($i = 0; $i < strlen($mima); $i ++) {
            $final_mima .= strpos($toconvert, $mima[$i]);
        }
        return $final_mima;
    }

    public function ChangePass($newpass)
    {
        $snoopy = new Snoopy;
        $snoopy->rawheaders['Cookie'] = $this->cookie;
        $url = "http://www.ecard.ldu.edu.cn/accountcpwd.action";
        $snoopy->submit($url);
        $this->saveMM();
        $passwd = $this->convertNewMM($this->_mm);
        //echo $passwd;
        $newpasswd = $this->convertNewMM($newpass);
        //echo $newpass;
        $snoopy = new Snoopy;
        $snoopy->rawheaders['Cookie'] = $this->cookie;
        $url = "http://www.ecard.ldu.edu.cn/accountDocpwd.action";
        $post = array('account' => $this->_account, 'passwd' => $passwd, 'newpasswd' => $newpasswd, 'newpasswd2' => $newpasswd);
        $snoopy->referer = "http://www.ecard.ldu.edu.cn/accountcpwd.action";
        $snoopy->submit($url, $post);
        $result = $snoopy->results;
        //echo $result;
        $mysql = new SaeMysql();
        $sql = "update `ecard` set mima='" . $newpass . "' where openid='" . $this->_openid . "'";
        $mysql->runSql($sql);
        preg_match_all('/<p class="biaotou">(.*)<.p>/iU', $result, $end);
        return $end[1][0];
    }

    public function doloss($passwd)
    {
        $snoopy = new Snoopy;
        $snoopy->rawheaders['Cookie'] = $this->cookie;
        $url = "http://www.ecard.ldu.edu.cn/accountloss.action";
        $snoopy->submit($url);
        $this->saveMM();
        $passwd = $this->convertNewMM($passwd);
        //echo $passwd;
        $snoopy = new Snoopy;
        $snoopy->rawheaders['Cookie'] = $this->cookie;
        $url = "http://www.ecard.ldu.edu.cn/accountDoLoss.action";
        $post = array('account' => $this->_account, 'passwd' => $passwd);
        $snoopy->referer = "http://www.ecard.ldu.edu.cn/accountloss.action";
        $snoopy->submit($url, $post);
        $result = $snoopy->results;
        //echo $result;        
        preg_match_all('/<p class="biaotou" >(.*)<.p>/iU', $result, $end);
        $end[1][0] = mb_convert_encoding($end[1][0], "UTF-8", "GBK");
        if ( $end[1][0] !== '密码错误' ) {
            $mysql = new SaeMysql();
            $sql = "update `ecard` set loss = 1 where openid='" . $this->_openid . "'";
            $mysql->runSql($sql);
        }
        return $end[1][0];
    }

    public function Getconsumption()
    {

        if ( ! empty( $this->_account ) ) {
            $snoopy = new Snoopy;
            $snoopy->rawheaders['Cookie'] = $this->cookie;
            $url = "http://202.194.48.115/accounttodatTrjnObject.action";
            $post = array('account' => $this->_account, 'inputObject' => 'all', 'Submit' => '+%C8%B7+%B6%A8+');
            $snoopy->submit($url, $post);
            $result = $snoopy->results;
            if ( ! empty( $result ) ) {
                preg_match_all('"<td\s{2,3}align=.(center|right).\s?>(.*)</td>"iU', $result, $today);
                preg_match('"<div align=.center.>.*?([\-|\+]\d{1,3}\.\d{1,2}).*?</div>"is', $result, $current);
                if ( ! empty( $today[2][7] ) ) {
                    $xiaofei = '';
                    for ($i = 7; ! empty( $today[2][$i] ); $i += 8) {
                        $today[2][$i] = mb_convert_encoding($today[2][$i], "UTF-8", "GBK");
                        $today[2][$i + 1] = mb_convert_encoding($today[2][$i + 1], "UTF-8", "GBK");
                        $today[2][$i + 2] = mb_convert_encoding($today[2][$i + 2], "UTF-8", "GBK");
                        $today[2][$i + 4] = mb_convert_encoding($today[2][$i + 4], "UTF-8", "GBK");
                        $xiaofei = $xiaofei . $today[2][$i] . ' ' . $today[2][$i + 1] . ' ' . $today[2][$i + 2] . ' ' . $today[2][$i + 4] . '元\n';
                    }
                    $xiaofei = $this->_name . "同学你好,今天你的消费如下:\n" . $xiaofei . "总共消费:" . $current[1] . "元";
                    $xiaofeinow = $today[2][7] . ' ' . $today[2][8] . ' ' . $today[2][9] . ' ' . $today[2][11] . '元';
                    $xiaofeinowtime = $today[2][7];
                    $xiaofeinowtime = str_replace('/', '-', $xiaofeinowtime);
                }
                else {
                    $xiaofei = $this->_name . "同学你好,系统暂未检测到今天的消费记录.";
                }
                $mysql = new SaeMysql();
                $result = $mysql->getData("SELECT * FROM `ecard` where openid='" . $this->_openid . "'");
                if ( $result[0]['xiaofeinowtime'] !== $xiaofeinowtime ) {
                    $sql = "update ecard set send = 1 where openid='" . $this->_openid . "'";
                    $mysql->runSql($sql);
                }
                $sql = "update ecard set xiaofeinowtime='" . $xiaofeinowtime . "',xiaofei_now='" . $xiaofeinow . "',xiaofei='" . $xiaofei . "' where openid='" . $this->_openid . "'";
                $mysql->runSql($sql);
                return true;
            }
            //if
        }
        //if
        return false;
    }
    //func

}

//$newecard = new ecard($mm,$xuehao);
//$newecard->login();
//$newecard->Getconsumption();

?>