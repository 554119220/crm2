<?php
/**
*新增价格促销
*/

class PromotionPriceAddRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.price.add";
	}
	
	/**促销开始时间，产品促销时间不能超过一个月！格式：yyyy-MM-dd HH:mm:ss*/
	private  $startDate; 
	/**促销结束时间，产品促销时间不能超过一个月！格式：yyyy-MM-dd HH:mm:ss*/
	private  $endDate; 
	/**产品串="产品id,折扣/促销价,单用户购买数量限制,总数量限制" 产品只能是普通产品，不能是系列产品。0为不限制。isPrice为0时存折扣值，为1时存促销价格值*/
	private  $productStr; 
	/**系列虚品产品串中的产品只能为系列虚品，子产品串中的产品只能为该系列虚品的子品。如果系列虚品产品串后一个子产品串也没有则添加所有子品促销。*/
	private  $productStrSerial; 
	/**是否附带设置免邮促销，1：是  0：否*/
	private  $isAttachShippingProm; 
	/**单独设置的邮费（该邮费只有在全场免邮的情况下才会生效），附带设置免邮时为0。isAttachShippingProm和postage同时为0时使用默认邮费*/
	private  $postage; 
	/**0：按折扣设置，1：按价格设置*/
	private  $isPrice; 

	public function getStartDate(){
		return $this->startDate;
	}
	public function getEndDate(){
		return $this->endDate;
	}
	public function getProductStr(){
		return $this->productStr;
	}
	public function getProductStrSerial(){
		return $this->productStrSerial;
	}
	public function getIsAttachShippingProm(){
		return $this->isAttachShippingProm;
	}
	public function getPostage(){
		return $this->postage;
	}
	public function getIsPrice(){
		return $this->isPrice;
	}

	public function setStartDate($startDate){
		$this->startDate = $startDate;
		$this->apiParas["startDate"] = $startDate;
	}
	public function setEndDate($endDate){
		$this->endDate = $endDate;
		$this->apiParas["endDate"] = $endDate;
	}
	public function setProductStr($productStr){
		$this->productStr = $productStr;
		$this->apiParas["productStr"] = $productStr;
	}
	public function setProductStrSerial($productStrSerial){
		$this->productStrSerial = $productStrSerial;
		$this->apiParas["productStrSerial"] = $productStrSerial;
	}
	public function setIsAttachShippingProm($isAttachShippingProm){
		$this->isAttachShippingProm = $isAttachShippingProm;
		$this->apiParas["isAttachShippingProm"] = $isAttachShippingProm;
	}
	public function setPostage($postage){
		$this->postage = $postage;
		$this->apiParas["postage"] = $postage;
	}
	public function setIsPrice($isPrice){
		$this->isPrice = $isPrice;
		$this->apiParas["isPrice"] = $isPrice;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
