<?php
/**
*自己联系物流（线下物流）发货（兼容淘宝）
*/

class LogisticsOfflineSendRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.offline.send";
	}
	
	/**卖家联系人地址库ID（暂不提供）*/
	private  $cancelId; 
	/**物流公司编码*/
	private  $companyCode; 
	/**feature参数格式<br> 范例: identCode=tid1:识别码1,识别码2|tid2:识别码3;machineCode=tid3:3C机器号A,3C机器号B<br> identCode为识别码的KEY,machineCode为3C的KEY,多个key之间用”;”分隔<br> “tid1:识别码1,识别码2|tid2:识别码3”为identCode对应的value。 "|"不同商品间的分隔符。<br>（暂不提供）*/
	private  $feature; 
	/**表明是否是拆单 1表示拆单 0表示不拆单，默认值0（暂不提供）*/
	private  $isSplit; 
	/**运单号*/
	private  $outSid; 
	/**商家的IP地址（暂不提供）*/
	private  $sellerIp; 
	/**卖家联系人地址库ID（暂不提供）*/
	private  $senderId; 
	/**需要拆单发货的子订单集合，为空表示不做拆单发货（暂不提供）*/
	private  $subTid; 
	/**交易ID*/
	private  $tid; 

	public function getCancelId(){
		return $this->cancelId;
	}
	public function getCompanyCode(){
		return $this->companyCode;
	}
	public function getFeature(){
		return $this->feature;
	}
	public function getIsSplit(){
		return $this->isSplit;
	}
	public function getOutSid(){
		return $this->outSid;
	}
	public function getSellerIp(){
		return $this->sellerIp;
	}
	public function getSenderId(){
		return $this->senderId;
	}
	public function getSubTid(){
		return $this->subTid;
	}
	public function getTid(){
		return $this->tid;
	}

	public function setCancelId($cancelId){
		$this->cancelId = $cancelId;
		$this->apiParas["cancelId"] = $cancelId;
	}
	public function setCompanyCode($companyCode){
		$this->companyCode = $companyCode;
		$this->apiParas["companyCode"] = $companyCode;
	}
	public function setFeature($feature){
		$this->feature = $feature;
		$this->apiParas["feature"] = $feature;
	}
	public function setIsSplit($isSplit){
		$this->isSplit = $isSplit;
		$this->apiParas["isSplit"] = $isSplit;
	}
	public function setOutSid($outSid){
		$this->outSid = $outSid;
		$this->apiParas["outSid"] = $outSid;
	}
	public function setSellerIp($sellerIp){
		$this->sellerIp = $sellerIp;
		$this->apiParas["sellerIp"] = $sellerIp;
	}
	public function setSenderId($senderId){
		$this->senderId = $senderId;
		$this->apiParas["senderId"] = $senderId;
	}
	public function setSubTid($subTid){
		$this->subTid = $subTid;
		$this->apiParas["subTid"] = $subTid;
	}
	public function setTid($tid){
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
