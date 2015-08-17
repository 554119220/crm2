<?php
/**
*虚拟团购产品更新
*/

class GroupVirtualProductUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.group.virtual.product.update";
	}
	
	/**1号商城产品ID*/
	private  $productId; 
	/**合作方团购ID*/
	private  $groupId; 
	/**团购产品名称(不超过300个字符)*/
	private  $groupCname; 
	/**团购产品分类ID*/
	private  $groupCategoryId; 
	/**团购价格(大于或等于0,最多两位小数)*/
	private  $groupPrice; 
	/**团购购买数量下限(大于0)*/
	private  $minStockNum; 
	/**团购购买数量上限*/
	private  $maxStockNum; 
	/**每人购买数量下限*/
	private  $minGroupNum; 
	/**每人购买数量上限(最大为99)*/
	private  $maxGroupNum; 
	/**团购预告时间(yyyy-MM-ddHH:mm:ss格式)*/
	private  $prepareTime; 
	/**团购开始时间(yyyy-MM-ddHH:mm:ss格式)*/
	private  $startTime; 
	/**团购结束时间(yyyy-MM-ddHH:mm:ss格式)*/
	private  $endTime; 
	/**活动地区(可选多个省,多个省用逗号分隔)*/
	private  $saleAreaId; 
	/**团购副标题*/
	private  $titleDescription; 
	/**券有效期开始时间*/
	private  $validStartTime; 
	/**券有效期结束时间*/
	private  $validEndTime; 
	/**设施列表*/
	private  $facilities; 
	/**酒店详情(产品介绍)-接机服务、服务设施、房间设施、休闲设施、餐饮设施、会议设施、周边交通、周边信息、特色信息、设施列表*/
	private  $serviceRegulations; 
	/**特别提示(购买须知)*/
	private  $hotelNote; 
	/**好评率*/
	private  $reviewGoodRate; 
	/**预约成功率*/
	private  $reservationSuccessRate; 
	/**是否支持退款（1：是、0：否）*/
	private  $refundable; 
	/**酒店类型*/
	private  $hotelType1; 
	/**用户评论信息*/
	private  $userReviewInfos; 
	/**门店信息*/
	private  $inshopDetailInfos; 
	/**团购二级分类ID*/
	private  $groupChildCategoryId; 
	/**产品文描*/
	private  $productDescription; 
	/**产品图片url*/
	private  $productImgUrls; 

	public function getProductId(){
		return $this->productId;
	}
	public function getGroupId(){
		return $this->groupId;
	}
	public function getGroupCname(){
		return $this->groupCname;
	}
	public function getGroupCategoryId(){
		return $this->groupCategoryId;
	}
	public function getGroupPrice(){
		return $this->groupPrice;
	}
	public function getMinStockNum(){
		return $this->minStockNum;
	}
	public function getMaxStockNum(){
		return $this->maxStockNum;
	}
	public function getMinGroupNum(){
		return $this->minGroupNum;
	}
	public function getMaxGroupNum(){
		return $this->maxGroupNum;
	}
	public function getPrepareTime(){
		return $this->prepareTime;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getEndTime(){
		return $this->endTime;
	}
	public function getSaleAreaId(){
		return $this->saleAreaId;
	}
	public function getTitleDescription(){
		return $this->titleDescription;
	}
	public function getValidStartTime(){
		return $this->validStartTime;
	}
	public function getValidEndTime(){
		return $this->validEndTime;
	}
	public function getFacilities(){
		return $this->facilities;
	}
	public function getServiceRegulations(){
		return $this->serviceRegulations;
	}
	public function getHotelNote(){
		return $this->hotelNote;
	}
	public function getReviewGoodRate(){
		return $this->reviewGoodRate;
	}
	public function getReservationSuccessRate(){
		return $this->reservationSuccessRate;
	}
	public function getRefundable(){
		return $this->refundable;
	}
	public function getHotelType1(){
		return $this->hotelType1;
	}
	public function getUserReviewInfos(){
		return $this->userReviewInfos;
	}
	public function getInshopDetailInfos(){
		return $this->inshopDetailInfos;
	}
	public function getGroupChildCategoryId(){
		return $this->groupChildCategoryId;
	}
	public function getProductDescription(){
		return $this->productDescription;
	}
	public function getProductImgUrls(){
		return $this->productImgUrls;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setGroupId($groupId){
		$this->groupId = $groupId;
		$this->apiParas["groupId"] = $groupId;
	}
	public function setGroupCname($groupCname){
		$this->groupCname = $groupCname;
		$this->apiParas["groupCname"] = $groupCname;
	}
	public function setGroupCategoryId($groupCategoryId){
		$this->groupCategoryId = $groupCategoryId;
		$this->apiParas["groupCategoryId"] = $groupCategoryId;
	}
	public function setGroupPrice($groupPrice){
		$this->groupPrice = $groupPrice;
		$this->apiParas["groupPrice"] = $groupPrice;
	}
	public function setMinStockNum($minStockNum){
		$this->minStockNum = $minStockNum;
		$this->apiParas["minStockNum"] = $minStockNum;
	}
	public function setMaxStockNum($maxStockNum){
		$this->maxStockNum = $maxStockNum;
		$this->apiParas["maxStockNum"] = $maxStockNum;
	}
	public function setMinGroupNum($minGroupNum){
		$this->minGroupNum = $minGroupNum;
		$this->apiParas["minGroupNum"] = $minGroupNum;
	}
	public function setMaxGroupNum($maxGroupNum){
		$this->maxGroupNum = $maxGroupNum;
		$this->apiParas["maxGroupNum"] = $maxGroupNum;
	}
	public function setPrepareTime($prepareTime){
		$this->prepareTime = $prepareTime;
		$this->apiParas["prepareTime"] = $prepareTime;
	}
	public function setStartTime($startTime){
		$this->startTime = $startTime;
		$this->apiParas["startTime"] = $startTime;
	}
	public function setEndTime($endTime){
		$this->endTime = $endTime;
		$this->apiParas["endTime"] = $endTime;
	}
	public function setSaleAreaId($saleAreaId){
		$this->saleAreaId = $saleAreaId;
		$this->apiParas["saleAreaId"] = $saleAreaId;
	}
	public function setTitleDescription($titleDescription){
		$this->titleDescription = $titleDescription;
		$this->apiParas["titleDescription"] = $titleDescription;
	}
	public function setValidStartTime($validStartTime){
		$this->validStartTime = $validStartTime;
		$this->apiParas["validStartTime"] = $validStartTime;
	}
	public function setValidEndTime($validEndTime){
		$this->validEndTime = $validEndTime;
		$this->apiParas["validEndTime"] = $validEndTime;
	}
	public function setFacilities($facilities){
		$this->facilities = $facilities;
		$this->apiParas["facilities"] = $facilities;
	}
	public function setServiceRegulations($serviceRegulations){
		$this->serviceRegulations = $serviceRegulations;
		$this->apiParas["serviceRegulations"] = $serviceRegulations;
	}
	public function setHotelNote($hotelNote){
		$this->hotelNote = $hotelNote;
		$this->apiParas["hotelNote"] = $hotelNote;
	}
	public function setReviewGoodRate($reviewGoodRate){
		$this->reviewGoodRate = $reviewGoodRate;
		$this->apiParas["reviewGoodRate"] = $reviewGoodRate;
	}
	public function setReservationSuccessRate($reservationSuccessRate){
		$this->reservationSuccessRate = $reservationSuccessRate;
		$this->apiParas["reservationSuccessRate"] = $reservationSuccessRate;
	}
	public function setRefundable($refundable){
		$this->refundable = $refundable;
		$this->apiParas["refundable"] = $refundable;
	}
	public function setHotelType1($hotelType1){
		$this->hotelType1 = $hotelType1;
		$this->apiParas["hotelType1"] = $hotelType1;
	}
	public function setUserReviewInfos($userReviewInfos){
		$this->userReviewInfos = $userReviewInfos;
		$this->apiParas["userReviewInfos"] = $userReviewInfos;
	}
	public function setInshopDetailInfos($inshopDetailInfos){
		$this->inshopDetailInfos = $inshopDetailInfos;
		$this->apiParas["inshopDetailInfos"] = $inshopDetailInfos;
	}
	public function setGroupChildCategoryId($groupChildCategoryId){
		$this->groupChildCategoryId = $groupChildCategoryId;
		$this->apiParas["groupChildCategoryId"] = $groupChildCategoryId;
	}
	public function setProductDescription($productDescription){
		$this->productDescription = $productDescription;
		$this->apiParas["productDescription"] = $productDescription;
	}
	public function setProductImgUrls($productImgUrls){
		$this->productImgUrls = $productImgUrls;
		$this->apiParas["productImgUrls"] = $productImgUrls;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
