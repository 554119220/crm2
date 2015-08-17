<?php
/**
*确认PO单
*/

class SupplierOrderPoConfirmRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.order.po.confirm";
	}
	
	/**被取消的采购单code*/
	private  $poCode; 
	/**快递单号*/
	private  $expressNo; 
	/**递送方式*/
	private  $deliveryMethod; 
	/**递送人姓名*/
	private  $deliveryPeople; 
	/**递送人电话*/
	private  $deliveryPeoplePhone; 
	/**预计送达时间*/
	private  $expectedDeliveryDate; 
	/**此值为json数组.其中 id：Long型，必填项，PO详情项ID； shipQty：Integer型，必填项，发货数量； lessRemark：String型，非必填项，包装发生变化的不一致的原因说明； enablePrint：Integer型，非必填项，是否打印（1打印，0不打印）； lessCode：Long型，非必填项，包装发生变化的不一致code； lessCode：不一致的原因code为下列值其一。 1：接受并确认可发货 2：销售区域限制 3：断货已停产 4：库存不足 5：缺货 6：其他*/
	private  $confirmitemList; 

	public function getPoCode(){
		return $this->poCode;
	}
	public function getExpressNo(){
		return $this->expressNo;
	}
	public function getDeliveryMethod(){
		return $this->deliveryMethod;
	}
	public function getDeliveryPeople(){
		return $this->deliveryPeople;
	}
	public function getDeliveryPeoplePhone(){
		return $this->deliveryPeoplePhone;
	}
	public function getExpectedDeliveryDate(){
		return $this->expectedDeliveryDate;
	}
	public function getConfirmitemList(){
		return $this->confirmitemList;
	}

	public function setPoCode($poCode){
		$this->poCode = $poCode;
		$this->apiParas["poCode"] = $poCode;
	}
	public function setExpressNo($expressNo){
		$this->expressNo = $expressNo;
		$this->apiParas["expressNo"] = $expressNo;
	}
	public function setDeliveryMethod($deliveryMethod){
		$this->deliveryMethod = $deliveryMethod;
		$this->apiParas["deliveryMethod"] = $deliveryMethod;
	}
	public function setDeliveryPeople($deliveryPeople){
		$this->deliveryPeople = $deliveryPeople;
		$this->apiParas["deliveryPeople"] = $deliveryPeople;
	}
	public function setDeliveryPeoplePhone($deliveryPeoplePhone){
		$this->deliveryPeoplePhone = $deliveryPeoplePhone;
		$this->apiParas["deliveryPeoplePhone"] = $deliveryPeoplePhone;
	}
	public function setExpectedDeliveryDate($expectedDeliveryDate){
		$this->expectedDeliveryDate = $expectedDeliveryDate;
		$this->apiParas["expectedDeliveryDate"] = $expectedDeliveryDate;
	}
	public function setConfirmitemList($confirmitemList){
		$this->confirmitemList = $confirmitemList;
		$this->apiParas["confirmitemList"] = $confirmitemList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
