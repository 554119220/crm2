<?php
/**
*提交联合发券活动申请
*/

class PromotionUnitecouponsActivitySubmitRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.unitecoupons.activity.submit";
	}
	
	/**活动名称*/
	private  $activityName; 
	/**开始时间(YYYY-MM-DD)*/
	private  $startTime; 
	/**结束时间(YYYY-MM-DD)*/
	private  $endTime; 
	/**活动类型(1：全体用户，2:指定类目,3:指定商品)*/
	private  $activityType; 
	/**持券商家id*/
	private  $referMerchantId; 
	/**申请抵用券关联的活动id*/
	private  $couponActivityId; 
	/**备注(申请商家填写的备注信息)*/
	private  $activityRemark; 
	/**活动关联的类目信息列表(当活动类型activityType的值为2时,为必须项） 格式为： 类目一id,类目二id */
	private  $categoryList; 
	/**活动关联的商品列表(当活动类型activityType的值为3时,为必须项） 格式为： 产品一id ,产品二id */
	private  $productList; 

	public function getActivityName(){
		return $this->activityName;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}
	public function getActivityType(){
		return $this->activityType;
	}
	public function getReferMerchantId(){
		return $this->referMerchantId;
	}
	public function getCouponActivityId(){
		return $this->couponActivityId;
	}
	public function getActivityRemark(){
		return $this->activityRemark;
	}
	public function getCategoryList(){
		return $this->categoryList;
	}
	public function getProductList(){
		return $this->productList;
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
	public function setActivityType($activityType){
		$this->activityType = $activityType;
		$this->apiParas["activityType"] = $activityType;
	}
	public function setReferMerchantId($referMerchantId){
		$this->referMerchantId = $referMerchantId;
		$this->apiParas["referMerchantId"] = $referMerchantId;
	}
	public function setCouponActivityId($couponActivityId){
		$this->couponActivityId = $couponActivityId;
		$this->apiParas["couponActivityId"] = $couponActivityId;
	}
	public function setActivityRemark($activityRemark){
		$this->activityRemark = $activityRemark;
		$this->apiParas["activityRemark"] = $activityRemark;
	}
	public function setCategoryList($categoryList){
		$this->categoryList = $categoryList;
		$this->apiParas["categoryList"] = $categoryList;
	}
	public function setProductList($productList){
		$this->productList = $productList;
		$this->apiParas["productList"] = $productList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
