<?php
/**
*新增满减促销
*/

class PromotionFullminusAddRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.fullminus.add";
	}
	
	/**促销开始时间，格式如示例，产品促销时间不能超过一个月！*/
	private  $startDate; 
	/**促销结束时间，格式如示例，产品促销时间不能超过一个月*/
	private  $endDate; 
	/**满减类型。1：商品   2:分类   3:分类+品牌,4:全场*/
	private  $fullMinusType; 
	/**促销活动广告语,最多只能输入50个字符,不能有特殊字符
['@', '#', '$', '%', '^', '&', '*', '<', '>','\'','\"', '/','＠','＃','￥','％','《','》','＆','‘','’','“','”']*/
	private  $promotionTitle; 
	/**每个订单是否允许重复参加活动，0:否  1:是*/
	private  $repeat; 
	/**每个账号可参加活动次数, =0:不限 >0限制次数,最大1000，大于1000自动设为1000*/
	private  $limitNumPerUser; 
	/**多级优惠类型: 1:只能参加一级 2:可重复参加。conditionValues只有1级时必须为1*/
	private  $joinLevelType; 
	/**优惠条件：每个账号单笔消费满多少元,多级优惠“，”拼接，递增，和contentValues一一对应,且对应的优惠金额必须小于优惠条件的50%。小数点后最多两位*/
	private  $conditionValues; 
	/**设置的促销普通商品id,以“;”分隔（当满减类型为1时 和productSerialIds不能同时为空，两者相加最多100个）*/
	private  $productIds; 
	/**设置的促销系列商品,如果后面没有子品id则默认添加所有子品 （当满减类型为1时和productIds不能同时为空）*/
	private  $productSerialIds; 
	/**类别ID 组合，“;”分隔（当满减类型为2时必须），最多50个*/
	private  $categoryIds; 
	/**品牌id:分类id 组合 ，  “;” “:”分隔   （当满减类型为3时必须），最多50对*/
	private  $brandCategoryIds; 
	/**满减类型为2或3时，需要排除的商品id，以“，”分隔，最多20个*/
	private  $excludeProductIds; 
	/**优惠金额：减现金XX元  多级优惠  “，”拼接,递增。小数点后最多两位*/
	private  $contentValues; 

	public function getStartDate(){
		return $this->startDate;
	}
	public function getEndDate(){
		return $this->endDate;
	}
	public function getFullMinusType(){
		return $this->fullMinusType;
	}
	public function getPromotionTitle(){
		return $this->promotionTitle;
	}
	public function getRepeat(){
		return $this->repeat;
	}
	public function getLimitNumPerUser(){
		return $this->limitNumPerUser;
	}
	public function getJoinLevelType(){
		return $this->joinLevelType;
	}
	public function getConditionValues(){
		return $this->conditionValues;
	}
	public function getProductIds(){
		return $this->productIds;
	}
	public function getProductSerialIds(){
		return $this->productSerialIds;
	}
	public function getCategoryIds(){
		return $this->categoryIds;
	}
	public function getBrandCategoryIds(){
		return $this->brandCategoryIds;
	}
	public function getExcludeProductIds(){
		return $this->excludeProductIds;
	}
	public function getContentValues(){
		return $this->contentValues;
	}

	public function setStartDate($startDate){
		$this->startDate = $startDate;
		$this->apiParas["startDate"] = $startDate;
	}
	public function setEndDate($endDate){
		$this->endDate = $endDate;
		$this->apiParas["endDate"] = $endDate;
	}
	public function setFullMinusType($fullMinusType){
		$this->fullMinusType = $fullMinusType;
		$this->apiParas["fullMinusType"] = $fullMinusType;
	}
	public function setPromotionTitle($promotionTitle){
		$this->promotionTitle = $promotionTitle;
		$this->apiParas["promotionTitle"] = $promotionTitle;
	}
	public function setRepeat($repeat){
		$this->repeat = $repeat;
		$this->apiParas["repeat"] = $repeat;
	}
	public function setLimitNumPerUser($limitNumPerUser){
		$this->limitNumPerUser = $limitNumPerUser;
		$this->apiParas["limitNumPerUser"] = $limitNumPerUser;
	}
	public function setJoinLevelType($joinLevelType){
		$this->joinLevelType = $joinLevelType;
		$this->apiParas["joinLevelType"] = $joinLevelType;
	}
	public function setConditionValues($conditionValues){
		$this->conditionValues = $conditionValues;
		$this->apiParas["conditionValues"] = $conditionValues;
	}
	public function setProductIds($productIds){
		$this->productIds = $productIds;
		$this->apiParas["productIds"] = $productIds;
	}
	public function setProductSerialIds($productSerialIds){
		$this->productSerialIds = $productSerialIds;
		$this->apiParas["productSerialIds"] = $productSerialIds;
	}
	public function setCategoryIds($categoryIds){
		$this->categoryIds = $categoryIds;
		$this->apiParas["categoryIds"] = $categoryIds;
	}
	public function setBrandCategoryIds($brandCategoryIds){
		$this->brandCategoryIds = $brandCategoryIds;
		$this->apiParas["brandCategoryIds"] = $brandCategoryIds;
	}
	public function setExcludeProductIds($excludeProductIds){
		$this->excludeProductIds = $excludeProductIds;
		$this->apiParas["excludeProductIds"] = $excludeProductIds;
	}
	public function setContentValues($contentValues){
		$this->contentValues = $contentValues;
		$this->apiParas["contentValues"] = $contentValues;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
