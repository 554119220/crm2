<?php
/**
 * Created by JetBrains PhpStorm.
 * 说明：post 和 get方法都可以使用
 * sdk 入口文件
 * User: denniszhu
 * Date: 12-8-13
 * Time: 下午4:02
 * To change this template use File | Settings | File Templates.
 */

require_once './PaiPaiOpenApiOauth.php';

// Begin使用者信息：
// 以下四项鉴权参数 ，需要使用人员自行填写自己申请到的的相关信息
    $uin = "1280863473";
    $appOAuthID = "700042973";
    $appOAuthkey = "hdUMwmU4P5jQtHpC";
    $accessToken = "ad39b7dbd59b87cda827223c0e520d6f";
// End使用者信息


$sdk = new PaiPaiOpenApiOauth($appOAuthID, $appOAuthkey, $accessToken, $uin);

// 设置开启调试模式。
// true为开启，开启后，在显示页面会打印相关信息；false为关闭，使用者可以将其关闭，关闭不影响结果
$sdk->setDebugOn(false);

// Begin参数设置:

// 注意 示例中可能的url为 http://api.paipai.com/deal/sellerSearchDealList.xhtml?a=1&b=2&c=3
// 设置 用户需要调用的腾讯开放平台提供的接口。此处，按照上一行中的url，则用户要输入/deal/sellerSearchDealList.xhtml，前面不加hostname。
$sdk->setApiPath("/deal/sellerSearchDealList.xhtml");//这个是用户需要调用的 接口函数

// 用户使用的提交数据的方法。post 和 get均可；以及字符集
$sdk->setMethod("get");//post
$sdk->setCharset("utf-8");//gbk

// 以下部分用于设置用户在调用相关接口时url中"?"之后的各个参数，如上述描述中的a=1&b=2&c=3
$params = &$sdk->getParams();//注意，这里使用的是引用，故可以直接使用
$params["sellerUin"] = $uin;
$params["zhongwen"] = "cn";
$params["pageSize"] = "30";
$params["tms_op"] = "admin@855006089";
$params["tms_opuin"] = $uin;
$params["tms_skey"] = "@WXOgdqq16";

//设置http请求接受的主机名，默认是 api.buy.qq.com。此处用户可不用修改
//$sdk->setHostName("apitest.buy.qq.com");
// End参数设置

//run
try {
     $response = $sdk->invoke();
     print_r("<br/>-----------response---------<br/>");
     print_r($response);
} catch(Exception $e) {
     printf("Request Failed. code:%d, msg:%s\n",$e->getCode(), $e->getMessage());
}
?>
