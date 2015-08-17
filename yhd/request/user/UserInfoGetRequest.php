<?php
/**
*获取用户信息
*/

class UserInfoGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.user.info.get";
	}
	
	/**当前页数*/
	private  $curPage; 
	/**每页的数量*/
	private  $pageRows; 
	/**起始时间(YYYY-MM-DD HH:mm:ss)*/
	private  $startTime; 
	/**截止时间(YYYY-MM-DD HH:mm:ss)。查询的时间跨度是1年。*/
	private  $endTime; 

	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}

	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setStartTime($startTime){
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}
	public function setEndTime($endTime){
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
