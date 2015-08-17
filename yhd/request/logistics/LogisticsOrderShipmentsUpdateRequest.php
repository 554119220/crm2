<?php
/**
*订单发货(更新订单物流信息)
*/

class LogisticsOrderShipmentsUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.logistics.order.shipments.update";
	}
	
	/**订单号(订单编码)*/
	private  $orderCode; 
	/**配送商ID(从获取物流信息接口中获取)*/
	private  $deliverySupplierId; 
	/**运单号(快递编号)*/
	private  $expressNbr; 

	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getDeliverySupplierId(){
		return $this->deliverySupplierId;
	}
	public function getExpressNbr(){
		return $this->expressNbr;
	}

	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setDeliverySupplierId($deliverySupplierId){
		$this->deliverySupplierId = $deliverySupplierId;
		$this->apiParas["deliverySupplierId"] = $deliverySupplierId;
	}
	public function setExpressNbr($expressNbr){
		$this->expressNbr = $expressNbr;
		$this->apiParas["expressNbr"] = $expressNbr;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
