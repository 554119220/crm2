<?php
/**
*申请商家查看联合发券活动列表
*/

class PromotionUnitecouponsActivityListGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.unitecoupons.activityList.get";
	}
	
	/***/
	private  $id; 
	/***/
	private  $activityName; 
	/***/
	private  $startTime; 
	/***/
	private  $endTime; 
	/***/
	private  $activityState; 
	/***/
	private  $activityType; 
	/***/
	private  $merchantName; 
	/***/
	private  $referMerchantId; 
	/***/
	private  $referMerchantName; 
	/***/
	private  $couponActivityId; 
	/***/
	private  $couponActivityName; 
	/***/
	private  $curPage; 
	/***/
	private  $pageRows; 

	public function getId(){
		return $this->id;
	}
	public function getActivityName(){
		return $this->activityName;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}
	public function getActivityState(){
		return $this->activityState;
	}
	public function getActivityType(){
		return $this->activityType;
	}
	public function getMerchantName(){
		return $this->merchantName;
	}
	public function getReferMerchantId(){
		return $this->referMerchantId;
	}
	public function getReferMerchantName(){
		return $this->referMerchantName;
	}
	public function getCouponActivityId(){
		return $this->couponActivityId;
	}
	public function getCouponActivityName(){
		return $this->couponActivityName;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}

	public function setId($id){
		$this->id = $id;
		$this->apiParas["id"] = $id;
	}
	public function setActivityName($activityName){
		$this->activityName = $activityName;
		$this->apiParas["activityName"] = $activityName;
	}
	public function setStartTime($startTime){
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}
	public function setEndTime($endTime){
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}
	public function setActivityState($activityState){
		$this->activityState = $activityState;
		$this->apiParas["activityState"] = $activityState;
	}
	public function setActivityType($activityType){
		$this->activityType = $activityType;
		$this->apiParas["activityType"] = $activityType;
	}
	public function setMerchantName($merchantName){
		$this->merchantName = $merchantName;
		$this->apiParas["merchantName"] = $merchantName;
	}
	public function setReferMerchantId($referMerchantId){
		$this->referMerchantId = $referMerchantId;
		$this->apiParas["referMerchantId"] = $referMerchantId;
	}
	public function setReferMerchantName($referMerchantName){
		$this->referMerchantName = $referMerchantName;
		$this->apiParas["referMerchantName"] = $referMerchantName;
	}
	public function setCouponActivityId($couponActivityId){
		$this->couponActivityId = $couponActivityId;
		$this->apiParas["couponActivityId"] = $couponActivityId;
	}
	public function setCouponActivityName($couponActivityName){
		$this->couponActivityName = $couponActivityName;
		$this->apiParas["couponActivityName"] = $couponActivityName;
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
