<?php
	/**产品API:添加商品图片*/
	header("Content-Type: text/html; charset=utf-8");
	
	include "YhdClient.php";
	
	//设置系统级参数
	$paramArray['checkCode'] = "21644426-49114-1161197138774056-1213948";
	$paramArray['merchantId'] = "2426";
	$paramArray['erp'] = "self";
	$paramArray['erpVer'] = "1.0";
	$paramArray['format'] = "xml";
	$paramArray['ver'] = "1.0";
	$paramArray['method'] = "yhd.product.img.upload";

	//设置应用级参数
	$paramArray['productId'] = "1714221";
	$paramArray['testId'] = "中文测试";

	$filePathArray[0] = "D:/test/我111.jpg";
	$filePathArray[1] = "D:/test/IMG_1995.jpg";
	$secret = "1234567890";

	$url = 'http://localhost:8080/router/api/rest/router';
	
	$yhdClient = new YhdClient();
	$result = $yhdClient->sendByPost($url, $paramArray, $filePathArray, $secret);
	print_r($result);
	
?>