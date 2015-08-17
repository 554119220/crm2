<?php

define('IN_ECS', true);
define('CURRDIR', dirname(__FILE__));
require(CURRDIR.'/includes/init.php');
require(CURRDIR.'/includes/lib_c2c.php');

header("Content-type:text/html; charset=utf-8");
date_default_timezone_set('Asia/Shanghai');
$now_time = time();

$url = array (
    'taobao'   => 'https://oauth.taobao.com/token',
    'taobao01' => 'https://oauth.taobao.com/token',
    'taobao02' => 'https://oauth.taobao.com/token',
    'taobao03' => 'https://oauth.taobao.com/token',
    'jingdong' => 'http://oauth.jd.com/oauth/token',
    'yhd'      => 'https://member.yhd.com/login/token.do',
    'suning'   => 'http://open.suning.com/api/oauth/token',
);

/* 将获取到的数据保存到相应的记录中 */
if (isset($_REQUEST['state'])) {
    $auth = require(CURRDIR."/{$_REQUEST['state']}/config.php");

    //请求参数
    $postfields = array(
        'grant_type'    => 'authorization_code',
        'client_id'     => $auth['appkey'],     // AppKey
        'client_secret' => $auth['secretKey'],  // secretKey
        'code'          => $_REQUEST['code'],   // 获取到的授权码
        'redirect_uri'  => 'http://192.168.1.217/crm2/admin/synchro.php' // 回调地址
    );

    $token = selfcurl($url[$_REQUEST['state']],$postfields);
    if ('jingdong' == $_REQUEST['state']) {
        $token = substr($token, 0, strrpos($token,',')).'}';
    }
    $token = json_decode($token, true);

    if (file_exists(CURRDIR."/{$_REQUEST['state']}/sk.php")) {
        unlink(CURRDIR."/{$_REQUEST['state']}/sk.php");
    }

    $config = var_export($token, true);
    if (file_put_contents(CURRDIR."/{$_REQUEST['state']}/sk.php", '<?php'."\n".'$sk = '.$config.';')) {
        unlink(CURRDIR."/{$_REQUEST['state']}.txt");
    }

    die('<script>window.close();</script>');
}

if ($_REQUEST['act'] == 'synchro') {
    $platform = $_REQUEST['platform'];
    if (file_exists($platform.'.txt')) {
        $code = file_get_contents($platform.'.txt');
    } else {
        die($json->encode(array('platform' => $platform)));
    }

    $auth = require(CURRDIR."/$platform/config.php");
    if ($code == 27) {
        $res = authorize($auth['appkey'], $platform);
        die($json->encode($res));
    }
}

/**
 * 获取第三方平台授权
 */
function authorize ($appkey, $platform)
{
    $tail = '?response_type=code&client_id=%s&redirect_uri=';
    $tail_var = 'http://192.168.1.217/crm2/admin/synchro.php&state=%s';
    $request_uri = array (
        'taobao'   => 'https://oauth.taobao.com/authorize',
        'taobao01' => 'https://oauth.taobao.com/authorize',
        'taobao02' => 'https://oauth.taobao.com/authorize',
        'taobao03' => 'https://oauth.taobao.com/authorize',
        'jingdong' => 'https://oauth.jd.com/oauth/authorize',
        'yhd'      => 'https://member.yhd.com/login/authorize.do',
        'suning'   => 'http://open.suning.com/api/oauth/authorize',
    );

    if ('jingdong' == $platform) {
        $res = array (
            'uri'      => $request_uri[$platform].sprintf($tail.'urn:ietf:wg:oauth:2.0:oob&state=%s', $appkey, $platform),
            'platform' => $platform,
        );
    } else {
        $res = array (
            'uri'      => $request_uri[$platform].sprintf($tail.$tail_var, $appkey, $platform),
            'platform' => $platform,
        );
    }

    return $res;
}

/**
 * 获取第三方平台sessionkey
 */
function selfcurl($url, $postFields = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (is_array($postFields) && 0 < count($postFields))
    {
        $postBodyString = "";
        foreach ($postFields as $k => $v)
        {
            $postBodyString .= "$k=" . urlencode($v) . "&"; 
        }
        unset($k, $v);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
    }
    $reponse = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch),0);
    } else {
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode){
            throw new Exception($reponse,$httpStatusCode);
        }
    }

    curl_close($ch);
    return $reponse;
}
