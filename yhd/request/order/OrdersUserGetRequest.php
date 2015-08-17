<?php
/**
*获取用户的订单信息
*/

class OrdersUserGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.orders.user.get";
	}
	
	/**用户ID*/
	private  $endUserId; 
	/**订单创建开始时间（yyyy-MM-dd HH:mm:ss）*/
	private  $startTime; 
	/**订单创建结束时间（yyyy-MM-dd HH:mm:ss）*/
	private  $endTime; 
	/**当前页数（默认1）*/
	private  $curPage; 
	/**每页显示记录数，默认50，最大100*/
	private  $pageRows; 

	public function getEndUserId(){
		return $this->endUserId;
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

	public function setEndUserId($endUserId){
		$this->endUserId = $endUserId;
		$this->apiParas["endUserId"] = $endUserId;
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
