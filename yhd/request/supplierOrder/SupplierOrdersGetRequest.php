<?php
/**
*查询订单列表
*/

class SupplierOrdersGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.orders.get";
	}
	
	/**订单code*/
	private  $orderCode; 
	/**订单开始时间（必须与结束时间同时给出或者都不给出，时间间隔不得大于31天）*/
	private  $orderStartTime; 
	/**订单结束时间(必须与开始时间同时给出或者都不给出，时间间隔不得大于31天)*/
	private  $orderEndTime; 
	/**订单状态（1：代发货，2：已发货，3：用户已收到货，4:已完成，5:订单已关闭）*/
	private  $orderStatus; 
	/**收货人名称*/
	private  $goodReceiverName; 
	/**收货人手机号*/
	private  $goodReceiverMobile; 
	/**页面显示记录数*/
	private  $pageRows; 
	/**当前页*/
	private  $curPage; 

	public function getOrderCode(){
		return $this->orderCode;
	}
	public function getOrderStartTime(){
		return $this->orderStartTime;
	}
	public function getOrderEndTime(){
		return $this->orderEndTime;
	}
	public function getOrderStatus(){
		return $this->orderStatus;
	}
	public function getGoodReceiverName(){
		return $this->goodReceiverName;
	}
	public function getGoodReceiverMobile(){
		return $this->goodReceiverMobile;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getCurPage(){
		return $this->curPage;
	}

	public function setOrderCode($orderCode){
		$this->orderCode = $orderCode;
		$this->apiParas["orderCode"] = $orderCode;
	}
	public function setOrderStartTime($orderStartTime){
		$this->orderStartTime = $orderStartTime;
		$this->apiParas["orderStartTime"] = $orderStartTime;
	}
	public function setOrderEndTime($orderEndTime){
		$this->orderEndTime = $orderEndTime;
		$this->apiParas["orderEndTime"] = $orderEndTime;
	}
	public function setOrderStatus($orderStatus){
		$this->orderStatus = $orderStatus;
		$this->apiParas["orderStatus"] = $orderStatus;
	}
	public function setGoodReceiverName($goodReceiverName){
		$this->goodReceiverName = $goodReceiverName;
		$this->apiParas["goodReceiverName"] = $goodReceiverName;
	}
	public function setGoodReceiverMobile($goodReceiverMobile){
		$this->goodReceiverMobile = $goodReceiverMobile;
		$this->apiParas["goodReceiverMobile"] = $goodReceiverMobile;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
