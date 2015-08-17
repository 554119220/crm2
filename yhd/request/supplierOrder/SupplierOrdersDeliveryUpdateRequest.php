<?php
/**
*批量订单发货
*/

class SupplierOrdersDeliveryUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.orders.delivery.update";
	}
	
	/**订单发货信息（配送公司：配送单号：订单code：是否直发，配送公司1：配送单号1：订单code1：是否直发）*/
	private  $deliveryOrderInfoList; 

	public function getDeliveryOrderInfoList(){
		return $this->deliveryOrderInfoList;
	}

	public function setDeliveryOrderInfoList($deliveryOrderInfoList){
		$this->deliveryOrderInfoList = $deliveryOrderInfoList;
		$this->apiParas["deliveryOrderInfoList"] = $deliveryOrderInfoList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
