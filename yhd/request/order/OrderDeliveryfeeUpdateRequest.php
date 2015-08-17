<?php
/**
*更新订单运费
*/

class OrderDeliveryfeeUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.order.deliveryfee.update";
	}
	
	/**订单编码。只有待支付状态（ORDER_WAIT_PAY）的订单，才可以修改运费*/
	private  $orderCode; 
	/**运费。修改的运费值要小于原有运费值。*/
	private  $orderDeliveryFee; 

	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getOrderDeliveryFee(){
		return $this->orderDeliveryFee;
	}

	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setOrderDeliveryFee($orderDeliveryFee){
		$this->orderDeliveryFee = $orderDeliveryFee;
		$this->apiParas["orderDeliveryFee"] = $orderDeliveryFee;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
