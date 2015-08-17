<?php
/**
*团购退款
*/

class GroupVirtualRefundUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.group.virtual.refund.update";
	}
	
	/**一号店订单code*/
	private  $orderCode; 
	/**合作方订单code*/
	private  $partnerOrderCode; 
	/**券流水号列表（用分号分隔）。注意：所有券流水号都退款成功，才能将updateCount设置为1，否则表示失败。*/
	private  $voucherIds; 

	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getPartnerOrderCode(){
		return $this->partnerOrderCode;
	}
	public function getVoucherIds(){
		return $this->voucherIds;
	}

	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setPartnerOrderCode($partnerOrderCode){
		$this->partnerOrderCode = $partnerOrderCode;
		$this->apiParas["partnerOrderCode"] = $partnerOrderCode;
	}
	public function setVoucherIds($voucherIds){
		$this->voucherIds = $voucherIds;
		$this->apiParas["voucherIds"] = $voucherIds;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
