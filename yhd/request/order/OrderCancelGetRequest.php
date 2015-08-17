<?php
/**
*查询申请取消订单信息
*/

class OrderCancelGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.order.cancel.get";
	}
	
	/**申请取消开始时间*/
	private  $orderCancelApplyTimeStart; 
	/**申请取消结束时间*/
	private  $orderCancelApplyTimeEnd; 
	/**处理状态*/
	private  $cancelStatus; 
	/**订单编码*/
	private  $orderCode; 

	public function getOrderCancelApplyTimeStart(){
		return $this->orderCancelApplyTimeStart;
	}
	public function getOrderCancelApplyTimeEnd(){
		return $this->orderCancelApplyTimeEnd;
	}
	public function getCancelStatus(){
		return $this->cancelStatus;
	}
	public function getOrderCode(){
		return $this->orderCode;
	}

	public function setOrderCancelApplyTimeStart($orderCancelApplyTimeStart){
		$this->orderCancelApplyTimeStart = $orderCancelApplyTimeStart;
		$this->apiParas["orderCancelApplyTimeStart"] = $orderCancelApplyTimeStart;
	}
	public function setOrderCancelApplyTimeEnd($orderCancelApplyTimeEnd){
		$this->orderCancelApplyTimeEnd = $orderCancelApplyTimeEnd;
		$this->apiParas["orderCancelApplyTimeEnd"] = $orderCancelApplyTimeEnd;
	}
	public function setCancelStatus($cancelStatus){
		$this->cancelStatus = $cancelStatus;
		$this->apiParas["cancelStatus"] = $cancelStatus;
	}
	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
