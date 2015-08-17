<?php
/**
*订单提货码确认(dsv发货确认之前使用)
*/

class SupplierOrderLogisticsConfirmRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.order.logistics.confirm";
	}
	
	/**订单Id*/
	private  $orderId; 
	/**提货码*/
	private  $logisticsCode; 

	public function getOrderId(){
		return $this->orderId;
	}
	public function getLogisticsCode(){
		return $this->logisticsCode;
	}

	public function setOrderId($orderId){
		$this->orderId = $orderId;
		$this->apiParas["orderId"] = $orderId;
	}
	public function setLogisticsCode($logisticsCode){
		$this->logisticsCode = $logisticsCode;
		$this->apiParas["logisticsCode"] = $logisticsCode;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
