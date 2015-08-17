<?php
/**
*查询合约机订单查询接口
*/

class OrderContractphoneGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.order.contractphone.get";
	}
	
	/**合约机创建开始时间*/
	private  $startTime; 
	/**合约机创建结束时间*/
	private  $endTime; 
	/**订单列表。多个订单好用“,”隔开*/
	private  $orderCodeList; 
	/**机主姓名*/
	private  $hostName; 
	/**客户选的手机号*/
	private  $mobilePhone; 
	/**合约机资料审核状态：0（初始化）， 1（后台确认）， 2（后台取消） ，3（前台取消），4（ 已完成），5（商家确认），6（商家取消）*/
	private  $informationStatus; 
	/**最小50，最大100，默认100*/
	private  $pageRows; 
	/**页码，默认值1*/
	private  $curPage; 

	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}
	public function getOrderCodeList(){
		return $this->orderCodeList;
	}
	public function getHostName(){
		return $this->hostName;
	}
	public function getMobilePhone(){
		return $this->mobilePhone;
	}
	public function getInformationStatus(){
		return $this->informationStatus;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getCurPage(){
		return $this->curPage;
	}

	public function setStartTime($startTime){
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}
	public function setEndTime($endTime){
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}
	public function setOrderCodeList($orderCodeList){
		$this->orderCodeList = $orderCodeList;
		$this->apiParas["orderCodeList"] = $orderCodeList;
	}
	public function setHostName($hostName){
		$this->hostName = $hostName;
		$this->apiParas["hostName"] = $hostName;
	}
	public function setMobilePhone($mobilePhone){
		$this->mobilePhone = $mobilePhone;
		$this->apiParas["mobilePhone"] = $mobilePhone;
	}
	public function setInformationStatus($informationStatus){
		$this->informationStatus = $informationStatus;
		$this->apiParas["informationStatus"] = $informationStatus;
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
