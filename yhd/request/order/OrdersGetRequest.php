<?php
/**
*获取订单列表
*/

class OrdersGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.orders.get";
	}
	
    /**
     * 订单状态（逗号分隔）: 
       ORDER_WAIT_PAY：已下单（货款未全收）、 
       ORDER_PAYED：已下单（货款已收）、 
       ORDER_WAIT_SEND：可以发货（已送仓库）、 
        ORDER_ON_SENDING：已出库（货在途）、 
        ORDER_RECEIVED：货物用户已收到、 
        ORDER_FINISH：订单完成、 
        ORDER_CANCEL：订单取消*/
    private  $orderStatusList; 
    /**日期类型(1：订单生成日期，2：订单付款日期，3：订单发货日期，4：订单收货日期，5：订单更新日期)*/
    private  $dateType; 
    /**查询开始时间*/
	private  $startTime; 
	/**查询结束时间(时间差为15天)*/
	private  $endTime; 
	/**当前页数*/
	private  $curPage; 
	/**每页显示记录数，默认50，最大100*/
	private  $pageRows; 

	public function getOrderStatusList(){
		return $this->orderStatusList;
	}
	public function getDateType(){
		return $this->dateType;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}

	public function setOrderStatusList($orderStatusList){
		$this->orderStatusList = $orderStatusList;
		$this->apiParas["orderStatusList"] = $orderStatusList;
	}
	public function setDateType($dateType){
		$this->dateType = $dateType;
		$this->apiParas["dateType"] = $dateType;
	}
	public function setStartTime($startTime){
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}
	public function setEndTime($endTime){
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
