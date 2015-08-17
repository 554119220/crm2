<?php
/**
*更新已发券的状态
*/

class GroupVirtualVoucherStatusUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.group.virtual.voucher.status.update";
	}
	
	/**券流水号*/
	private  $voucherId; 
	/**券状态。1：已使用*/
	private  $voucherStatus; 
	/**一号店订单code*/
	private  $orderCode; 
	/**合作方订单code*/
	private  $partnerOrderCode; 

	public function getVoucherId(){
		return $this->voucherId;
	}
	public function getVoucherStatus(){
		return $this->voucherStatus;
	}
	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getPartnerOrderCode(){
		return $this->partnerOrderCode;
	}

	public function setVoucherId($voucherId){
		$this->voucherId = $voucherId;
		$this->apiParas["voucherId"] = $voucherId;
	}
	public function setVoucherStatus($voucherStatus){
		$this->voucherStatus = $voucherStatus;
		$this->apiParas["voucherStatus"] = $voucherStatus;
	}
	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setPartnerOrderCode($partnerOrderCode){
		$this->partnerOrderCode = $partnerOrderCode;
		$this->apiParas["partnerOrderCode"] = $partnerOrderCode;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
