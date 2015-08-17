<?php
/**
*编辑退货
*/

class RefundUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.refund.update";
	}
	
	/**退货单编码*/
	private  $refundCode; 
	/**产品Id*/
	private  $productId; 
	/**退货数量。它和退款金额同时输入，或者同时不输入*/
	private  $returnNum; 
	/**退款金额（不含邮费）。它和退货数量同时输入，或者同时不输入。
退款金额不能大于（退货数量 乘以商品单价）*/
	private  $productAmount; 
	/**是否退运费。0:不退运费，1：退运费。*/
	private  $isDeliveryFee; 
	/**备注信息*/
	private  $remark; 

	public function getRefundCode(){
		return $this->refundCode;
	}
	public function getProductId(){
		return $this->productId;
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
	public function getRemark(){
		return $this->remark;
	}

	public function setRefundCode($refundCode){
		$this->refundCode = $refundCode;
		$this->apiParas["refundCode"] = $refundCode;
	}
	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
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
	public function setRemark($remark){
		$this->remark = $remark;
		$this->apiParas["remark"] = $remark;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
