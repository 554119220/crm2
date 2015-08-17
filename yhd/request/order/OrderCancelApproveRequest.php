<?php
/**
*同意取消订单
*/

class OrderCancelApproveRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.order.cancel.approve";
	}
	
	/**订单号(订单编码)*/
	private  $orderCode; 

	public function getOrderCode(){
		return $this->orderCode;
	}

	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
