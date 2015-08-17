<?php
/**
*团购订单信息确认
*/

class GroupBuyOrderVerifyRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.group.buy.order.verify";
	}
	
	/**1号店订单号码*/
	private  $orderCode; 
	/**合作方订单号码*/
	private  $partnerOrderCode; 
	/**订单金额*/
	private  $orderAmount; 
	/**订单生成时间(格式：yyyy-MM-ddHH:mm:ss)*/
	private  $orderCreateTime; 

	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getPartnerOrderCode(){
		return $this->partnerOrderCode;
	}
	public function getOrderAmount(){
		return $this->orderAmount;
	}
	public function getOrderCreateTime(){
		return $this->orderCreateTime;
	}

	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setPartnerOrderCode($partnerOrderCode){
		$this->partnerOrderCode = $partnerOrderCode;
		$this->apiParas["partnerOrderCode"] = $partnerOrderCode;
	}
	public function setOrderAmount($orderAmount){
		$this->orderAmount = $orderAmount;
		$this->apiParas["orderAmount"] = $orderAmount;
	}
	public function setOrderCreateTime($orderCreateTime){
		$this->orderCreateTime = $orderCreateTime;
		$this->apiParas["orderCreateTime"] = $orderCreateTime;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
