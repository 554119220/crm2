<?php
/**
*无需物流（虚拟）发货处理（兼容淘宝）
*/

class LogisticsDummySendRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.dummy.send";
	}
	
	/**交易ID */
	private  $tid; 
	/**feature参数格式 范例: identCode=tid1:识别码1,识别码2|tid2:识别码3;machineCode=tid3:3C机器号A,3C机器号B identCode为识别码的KEY,machineCode为3C的KEY,多个key之间用”;”分隔(暂不提供)*/
	private  $feature; 
	/**商家的IP地址 （暂不提供）*/
	private  $sellerIp ; 

	public function getTid(){
		return $this->tid;
	}
	public function getFeature(){
		return $this->feature;
	}
	public function getSellerIp (){
		return $this->sellerIp ;
	}

	public function setTid($tid){
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}
	public function setFeature($feature){
		$this->feature = $feature;
		$this->apiParas["feature"] = $feature;
	}
	public function setSellerIp ($sellerIp ){
		$this->sellerIp  = $sellerIp ;
		$this->apiParas["sellerIp "] = $sellerIp ;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
