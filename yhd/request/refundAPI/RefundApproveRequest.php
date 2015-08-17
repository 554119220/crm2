<?php
/**
*批准退货
*/

class RefundApproveRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.refund.approve";
	}
	
	/**退货单编码*/
	private  $refundCode; 
	/**退货数量。此参数与退货金额同时输入，或者同时不输入。*/
	private  $returnNum; 
	/**退款金额（不含邮费）。此参数与退货数量同时输入，或者同时不输入。
退款金额不能大于（退货数量 乘以商品单价）*/
	private  $productAmount; 
	/**是否退运费。0:不退运费，1：退运费。*/
	private  $isDeliveryFee; 
	/**是否寄回。0:不寄回，1：寄回。如果不寄回，则直接退款。*/
	private  $sendBackType; 
	/**是否使用默认联系人。0:不使用，1：使用。使用默认联系时，下面的参数联系方式等参数可以不填。否则，必填。*/
	private  $isDefaultContactName; 
	/**联系人名称*/
	private  $contactName; 
	/**联系人电话*/
	private  $contactPhone; 
	/**联系人地址*/
	private  $sendBackAddress; 
	/**备注信息*/
	private  $remark; 

	public function getRefundCode(){
		return $this->refundCode;
	}
	public function getReturnNum(){
		return $this->returnNum;
	}
	public function getProductAmount(){
		return $this->productAmount;
	}
	public function getIsDeliveryFee(){
		return $this->isDeliveryFee;
	}
	public function getSendBackType(){
		return $this->sendBackType;
	}
	public function getIsDefaultContactName(){
		return $this->isDefaultContactName;
	}
	public function getContactName(){
		return $this->contactName;
	}
	public function getContactPhone(){
		return $this->contactPhone;
	}
	public function getSendBackAddress(){
		return $this->sendBackAddress;
	}
	public function getRemark(){
		return $this->remark;
	}

	public function setRefundCode($refundCode){
		$this->refundCode = $refundCode;
		$this->apiParas["refundCode"] = $refundCode;
	}
	public function setReturnNum($returnNum){
		$this->returnNum = $returnNum;
		$this->apiParas["returnNum"] = $returnNum;
	}
	public function setProductAmount($productAmount){
		$this->productAmount = $productAmount;
		$this->apiParas["productAmount"] = $productAmount;
	}
	public function setIsDeliveryFee($isDeliveryFee){
		$this->isDeliveryFee = $isDeliveryFee;
		$this->apiParas["isDeliveryFee"] = $isDeliveryFee;
	}
	public function setSendBackType($sendBackType){
		$this->sendBackType = $sendBackType;
		$this->apiParas["sendBackType"] = $sendBackType;
	}
	public function setIsDefaultContactName($isDefaultContactName){
		$this->isDefaultContactName = $isDefaultContactName;
		$this->apiParas["isDefaultContactName"] = $isDefaultContactName;
	}
	public function setContactName($contactName){
		$this->contactName = $contactName;
		$this->apiParas["contactName"] = $contactName;
	}
	public function setContactPhone($contactPhone){
		$this->contactPhone = $contactPhone;
		$this->apiParas["contactPhone"] = $contactPhone;
	}
	public function setSendBackAddress($sendBackAddress){
		$this->sendBackAddress = $sendBackAddress;
		$this->apiParas["sendBackAddress"] = $sendBackAddress;
	}
	public function setRemark($remark){
		$this->remark = $remark;
		$this->apiParas["remark"] = $remark;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
