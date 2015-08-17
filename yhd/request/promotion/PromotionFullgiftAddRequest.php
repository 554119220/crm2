<?php
/**
*新增满就送赠品促销
*/

class PromotionFullgiftAddRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.fullgift.add";
	}
	
	/**促销开始时间，格式：yyyy-MM-dd HH:mm:ss，促销开始时间必须在当前时间之后！ 产品促销时间不能超过一个月！*/
	private  $startDate; 
	/**促销结束时间，格式：yyyy-MM-dd HH:mm:ss，产品促销时间不能超过一个月！*/
	private  $endDate; 
	/**满赠送类型。1:商品；2:分类；3:分类 品牌；4:全场（当为4时conditionType必须为1）*/
	private  $fullGiftType; 
	/**促销活动广告语,最多只能输入50个字符,不能有特殊字符
<xmp>['@', '#', '$', '%', '^', '&', '*', '<', '>','\'','\"', '/','＠','＃','￥','％','《','》','＆','‘','’','“','”']</xmp>*/
	private  $promotionTitle; 
	/**每个订单是否允许重复参加活动，0:否；1:是*/
	private  $repeat; 
	/**每个账号可参加活动次数,=0:不限；>0限制次数,最大1000，大于1000自动设为1000*/
	private  $limitNumPerUser; 
	/**优惠条件：每个账号单笔消费满多少元或者多少件送赠品。为满多少元时小数点后最多两位*/
	private  $conditionValue; 
	/**赠送数量限制类型。0:送完为止；1:限定总赠品数量；2:限定每天赠品数量。为0时产品串giftProductStrs里的“数量限制”必须为0*/
	private  $totalLimitType; 
	/**设置的促销普通商品id,以“;”分隔（当满赠类型为1时，productIds和productSerialIds不能同时为空，两者相加最多100个）*/
	private  $productIds; 
	/**设置的促销系列商品,格式为：系列商品之间用分号分隔，括号内是此系列商品的子品，子品间用逗号分隔。如果后面没有子品id则默认添加所有子品。 当满赠类型为1时，productSerialIds和productIds不能同时为空。并且普通商品id   系列商品id个数最多100个。*/
	private  $productSerialIds; 
	/**类别id列表，多个类别id之间用分号分隔，最多50个。当满赠送类型为2时,此参数必选。*/
	private  $categoryIds; 
	/**品牌id:分类id组合列表，品牌id和分类id之间用冒号分隔，多个（品牌id:分类id）之间用分号分隔，最多50对。当满赠送类型为3时，此参数必选。*/
	private  $brandCategoryIds; 
	/**满赠送类型为2或3时，需要排除的商品id列表，此商品类型为普通商品、系列商品子品。 各商品id之间用逗号分隔，最多20个。*/
	private  $excludeProductIds; 
	/**优惠类型：1-部分商品购满X元；2-部分商品购满X件*/
	private  $conditionType; 
	/**赠送的产品信息。产品串=“产品id,单用户赠送数量,赠送数量限制”，产品串最多3个。赠品的产品类型为：普通产品、系列商品虚品、系列商品子品，该商品必须可销且可见。单用户赠送数量不大于9 个；“数量限制”不能大于1000000，否则自动设为1000000。*/
	private  $giftProductStrs; 

	public function getStartDate(){
		return $this->startDate;
	}
	public function getEndDate(){
		return $this->endDate;
	}
	public function getFullGiftType(){
		return $this->fullGiftType;
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
	public function getConditionValue(){
		return $this->conditionValue;
	}
	public function getTotalLimitType(){
		return $this->totalLimitType;
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
	public function getConditionType(){
		return $this->conditionType;
	}
	public function getGiftProductStrs(){
		return $this->giftProductStrs;
	}

	public function setStartDate($startDate){
		$this->startDate = $startDate;
		$this->apiParas["startDate"] = $startDate;
	}
	public function setEndDate($endDate){
		$this->endDate = $endDate;
		$this->apiParas["endDate"] = $endDate;
	}
	public function setFullGiftType($fullGiftType){
		$this->fullGiftType = $fullGiftType;
		$this->apiParas["fullGiftType"] = $fullGiftType;
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
	public function setConditionValue($conditionValue){
		$this->conditionValue = $conditionValue;
		$this->apiParas["conditionValue"] = $conditionValue;
	}
	public function setTotalLimitType($totalLimitType){
		$this->totalLimitType = $totalLimitType;
		$this->apiParas["totalLimitType"] = $totalLimitType;
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
	public function setConditionType($conditionType){
		$this->conditionType = $conditionType;
		$this->apiParas["conditionType"] = $conditionType;
	}
	public function setGiftProductStrs($giftProductStrs){
		$this->giftProductStrs = $giftProductStrs;
		$this->apiParas["giftProductStrs"] = $giftProductStrs;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
