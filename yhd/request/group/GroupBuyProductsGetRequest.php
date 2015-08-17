<?php
/**
*团购报名查询
*/

class GroupBuyProductsGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.group.buy.products.get";
	}
	
	/**当前页数*/
	private  $curPage; 
	/**每页显示记录数，默认50，最大100*/
	private  $pageRows; 
	/**团购开始时间查询起始时间(yyyy-MM-ddHH:mm:ss格式)*/
	private  $startTimeBegin; 
	/**团购开始时间查询结束时间(yyyy-MM-ddHH:mm:ss格式)*/
	private  $startTimeEnd; 
	/**团购结束时间查询起始时间(yyyy-MM-ddHH:mm:ss格式)*/
	private  $endTimeBegin; 
	/**团购结束时间查询截止时间(yyyy-MM-ddHH:mm:ss格式)*/
	private  $endTimeEnd; 
	/**团购状态(WAIT_VERIFYING:待审核;VERIFY_REFUESD:审核拒绝;VERIFY_PASSED:审核通过;GROUPON:团购中;GROUPON_SUCCESS:团购中-成功;GROUPON_FULLED:团购中-人数满;FINISHED_FAILUE:结束-失败;FINISHED_SUCCESS:结束-成功;GROUP_FINISHED:处理完毕)*/
	private  $groupStatus; 

	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getStartTimeBegin(){
		return $this->startTimeBegin;
	}
	public function getStartTimeEnd(){
		return $this->startTimeEnd;
	}
	public function getEndTimeBegin(){
		return $this->endTimeBegin;
	}
	public function getEndTimeEnd(){
		return $this->endTimeEnd;
	}
	public function getGroupStatus(){
		return $this->groupStatus;
	}

	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setStartTimeBegin($startTimeBegin){
		$this->startTimeBegin = $startTimeBegin;
		$this->apiParas["startTimeBegin"] = $startTimeBegin;
	}
	public function setStartTimeEnd($startTimeEnd){
		$this->startTimeEnd = $startTimeEnd;
		$this->apiParas["startTimeEnd"] = $startTimeEnd;
	}
	public function setEndTimeBegin($endTimeBegin){
		$this->endTimeBegin = $endTimeBegin;
		$this->apiParas["endTimeBegin"] = $endTimeBegin;
	}
	public function setEndTimeEnd($endTimeEnd){
		$this->endTimeEnd = $endTimeEnd;
		$this->apiParas["endTimeEnd"] = $endTimeEnd;
	}
	public function setGroupStatus($groupStatus){
		$this->groupStatus = $groupStatus;
		$this->apiParas["groupStatus"] = $groupStatus;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
