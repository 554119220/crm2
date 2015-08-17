<?php
/**
*根据订单编号获取订单详情
*/

class SbyUserOrderDetailGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.sby.user.order.detail.get";
	}
	
	/**订单号*/
	private  $orderNo; 

	public function getOrderNo(){
		return $this->orderNo;
	}

	public function setOrderNo($orderNo){
		$this->orderNo = $orderNo;
		$this->apiParas["orderNo"] = $orderNo;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
