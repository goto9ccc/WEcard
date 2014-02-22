<?php
include_once( "snoopy.class.php" );
define( "Account", "ludongzhushou@163.com" );
define( "PassWord", "md5(mima)" );


class __user_remote_opera
{

    private $cookie;
    private $_cookiename;
    private $_cookieexpired = 3600;
    private $_account;
    private $_password;
    private $debug;
    private $_logcallback;
    private $_token;

    public function __construct()
    {
    }


    public function send($id, $content)
    {
        $send_snoopy = new Snoopy;
        $cookie = $this->read('cookie.log');
        $post = array();
        $post['tofakeid'] = $id;
        $post['type'] = 1;
        $post['token'] = $this->_token;
        $post['content'] = $content;
        #$post['error'] = false;
        $post['ajax'] = 1;
        $snoopy->agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36";
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/singlesendpage?t=message/send&action=index&tofakeid=$id&token={$this->_token}&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $cookie;
        $submit = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
        $send_snoopy->submit($submit, $post);
        $this->log($send_snoopy->results);
        return $send_snoopy->results;
    }


    private function log($log)
    {
        if ( $this->debug && function_exists($this->_logcallback) ) {
            if ( is_array($log) ) {
                $log = print_r($log, true);
            }
            return call_user_func($this->_logcallback, $log);
        }
    }

    public function checkValid()
    {
        $send_snoopy = new Snoopy;
        $cookie = $this->read('cookie.log');
        if ( ! $cookie || ! $this->_token ) {
            return false;
        }
        $post = array('ajax' => 1, 'token' => $this->_token);
        $submit = "https://mp.weixin.qq.com/cgi-bin/getregions?id=1017&t=ajax-getregions&lang=zh_CN";
        $send_snoopy->rawheaders['Cookie'] = $cookie;
        $send_snoopy->submit($submit, $post);
        $result = $send_snoopy->results;
        if ( json_decode($result, 1) ) {
            return true;
        }
        else {
            return false;
        }
    }


    public function keepLive()
    {
        if ( ! $this->checkValid() ) {
            echo "login...";
            return ( true === $this->loginWP() );
        }
        echo "already login";
        return false;
    }

    public function loginWP()
    {
        $snoopy = new Snoopy;
        $send_snoopy = new Snoopy;
        $submit = "https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
        $post["username"] = Account;
        $post["pwd"] = PassWord;
        $post["f"] = "json";
        $post["imgcode"] = "";
        $snoopy->referer = "https://mp.weixin.qq.com/";
        $snoopy->rawheaders["X-Requested-With"] = "XMLHttpRequest";
        $snoopy->agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36";
        $snoopy->submit($submit, $post);
        $result = json_decode($snoopy->results, true);
        $cookie = '';
        $this->log($snoopy->results);
        $result = json_decode($snoopy->results, true);
        if ( $result['ErrCode'] != 0 ) {
            return false;
        }
        foreach ($snoopy->headers as $key => $value) {
            $value = trim($value);
            if ( preg_match('/^set-cookie:[\s]+([^=]+)=([^;]+)/i', $value, $match) ) {
                $cookie .= $match[1] . '=' . $match[2] . '; ';
            }
        }
        preg_match("/token=(\d+)/i", $result['ErrMsg'], $matches);
        if ( $matches ) {
            $this->_token = $matches[1];
            $this->log('token:' . $this->_token);
        }
        $this->saveCookie('cookie.log', $cookie);
        return $cookie;
    }

    public function getMsg($lastid = 0, $offset = 0, $perpage = 50, $day = 0, $today = 0, $star = 0)
    {
        $send_snoopy = new Snoopy;
        $cookie = $this->read('cookie.log');
        $send_snoopy->rawheaders['Cookie'] = $cookie;
        $send_snoopy->referer = 'https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=0&token=' . $this->_token . '&lang=zh_CN&filterivrmsg=1';
        $lastid = $lastid === 0 ? '' : $lastid;
        $submit = 'https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=7&token=' . $this->_token . '&lang=zh_CN&filterivrmsg=1';
        $post = array('ajax' => 1, 'token' => $this->_token);
        $send_snoopy->submit($submit);
        $this->log($send_snoopy->results);
        preg_match('/"msg_item":\[(.*)\]}\).msg_item/isU', $send_snoopy->results, $matches);
        if ( ! $matches ) {
            echo 'failed';
            return false;
        }
        return $matches[1];
    }


    public function saveCookie($filename, $content)
    {
        $__sae = new SaeStorage();
        $fp = $__sae->write("new", $filename, $content);
    }

    public function getCookie($filename)
    {
        $__sae = new SaeStorage();
        $fp = $__sae->write("new", $filename, $content);
    }


    public function getNewMsgNum($lastid = 0)
    {
        $send_snoopy = new Snoopy;
        $send_snoopy->rawheaders['Cookie'] = $this->cookie;
        $send_snoopy->referer = "https://mp.weixin.qq.com/cgi-bin/getmessage?t=wxm-message&lang=zh_CN&count=50&token=" . $this->_token;
        $submit = "https://mp.weixin.qq.com/cgi-bin/getnewmsgnum?t=ajax-getmsgnum&lastmsgid=" . $lastid;
        $post = array('ajax' => 1, 'token' => $this->_token);
        $send_snoopy->submit($submit, $post);
        $this->log($send_snoopy->results);
        $result = json_decode($send_snoopy->results, 1);
        if ( ! $result ) {
            return false;
        }
        return intval($result['newTotalMsgCount']);
    }

    public function write($filename, $content)
    {
        $__sae = new SaeStorage();
        $fp = $__sae->write("new", $filename, $content);
    }

    // 读文件
    public function read($filename)
    {
        $__sae = new SaeStorage();
        if ( $__sae->fileExists("new", $filename) ) {
            $attr = $__sae->getAttr('new', 'cookie.log');
            $mtime = $attr['datetime'];
            print_r($mtime < time() - 3600);
            $data = '';
            if ( $mtime < time() - 3600 ) {
                $data = '';
            }
            else {
                $data = $__sae->read("new", $filename);
            }
        }
        else {
            $data = '';
        }
        if ( $data ) {
            $send_snoopy = new Snoopy();
            $send_snoopy->rawheaders['Cookie'] = $data;
            $send_snoopy->maxredirs = 0;
            $url = "https://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-index&lang=zh_CN";
            $send_snoopy->fetch($url);
            $header = implode(',', $send_snoopy->headers);
            $this->log('header:' . print_r($send_snoopy->headers, true));
            preg_match("/token=(\d+)/i", $header, $matches);
            if ( empty( $matches ) ) {
                return $this->loginWP();
            }
            else {
                $this->_token = $matches[1];
                $this->log('token:' . $this->_token);
                return $data;
            }

        }
        else {
            return $this->loginWP();
        }
    }
}

?>