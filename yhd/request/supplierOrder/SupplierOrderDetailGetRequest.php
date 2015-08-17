<?php
/**
*查询订单详情
*/

class SupplierOrderDetailGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.order.detail.get";
	}
	
	/**订单Id*/
	private  $orderId; 
	/**是否为历史订单。true：是；false：不是。*/
	private  $isHistory; 

	public function getOrderId(){
		return $this->orderId;
	}
	public function getIsHistory(){
		return $this->isHistory;
	}

	public function setOrderId($orderId){
		$this->orderId = $orderId;
		$this->apiParas["orderId"] = $orderId;
	}
	public function setIsHistory($isHistory){
		$this->isHistory = $isHistory;
		$this->apiParas["isHistory"] = $isHistory;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
