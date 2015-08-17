<?php
/**
*修改物流公司和运单号（兼容淘宝）
*/

class LogisticsConsignResendRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.consign.resend";
	}
	
	/**交易ID */
	private  $tid; 
	/**子订单号的列表(暂不支持)*/
	private  $subTid; 
	/**表明是否是拆单，默认值0，1表示拆单（暂不支持）*/
	private  $isSplit; 
	/**运单号.具体一个物流公司的真实运单号码。*/
	private  $outSid; 
	/**物流公司代码.如"POST"就代表中国邮政,"ZJS"就代表宅急送*/
	private  $companyCode; 
	/**feature参数格式(暂不提供)*/
	private  $feature; 
	/**商家的IP地址 (暂不提供)*/
	private  $sellerIp ; 

	public function getTid(){
		return $this->tid;
	}
	public function getSubTid(){
		return $this->subTid;
	}
	public function getIsSplit(){
		return $this->isSplit;
	}
	public function getOutSid(){
		return $this->outSid;
	}
	public function getCompanyCode(){
		return $this->companyCode;
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
	public function setSubTid($subTid){
		$this->subTid = $subTid;
		$this->apiParas["subTid"] = $subTid;
	}
	public function setIsSplit($isSplit){
		$this->isSplit = $isSplit;
		$this->apiParas["isSplit"] = $isSplit;
	}
	public function setOutSid($outSid){
		$this->outSid = $outSid;
		$this->apiParas["outSid"] = $outSid;
	}
	public function setCompanyCode($companyCode){
		$this->companyCode = $companyCode;
		$this->apiParas["companyCode"] = $companyCode;
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
