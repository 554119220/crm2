<?php
/**
*团购产品新增
*/

class GroupVirtualProductAddRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.group.virtual.product.add";
	}
	
	/**产品类别ID(从获取授权产品类别yhd.category.products.get接口中获取叶子ID)*/
	private  $categoryId; 
	/**商家产品类别ID列表(从获取商家产品类别yhd.category.merchant.products.get接口中获取叶子ID)*/
	private  $merchantCategoryId; 
	/**产品名称(产品名称开头必须以品牌名或品牌名加英文空格开头,不超过100个字符)*/
	private  $productCname; 
	/**产品名称副标题(不超过100个字符)*/
	private  $productSubTitle; 
	/**产品名称前缀(不超过10个字符)*/
	private  $productNamePrefix; 
	/**品牌ID(从获取授权品牌yhd.category.brands.get接口中获取)*/
	private  $brandId; 
	/**市场价(最多两位小数)*/
	private  $productMarketPrice; 
	/**销售价(不能大于市场价,最多两位小数)*/
	private  $productSalePrice; 
	/**重量(毛重KG,最多两位小数)*/
	private  $weight; 
	/**库存(大于或等于0)*/
	private  $virtualStockNum; 
	/**是否可销(0否;1是)*/
	private  $canSale; 
	/**产品描述(不超过300kb)*/
	private  $productDescription; 
	/**是否虚拟商品(是或否)*/
	private  $electronicCerticate; 
	/**产品属性列表(每个属性之间用逗号分隔,属性ID和属性值之间用冒号分隔。如属性ID:属性值),属性id不能重复*/
	private  $prodAttributeInfoList; 
	/**属性项列表:
1.每个<b>属性值对</b>以逗号分隔（<b>属性值对</b>：由属性id、选项id、子选项id组成）；
2.属性类型为单选且没有子属性，<b>属性值对</b>以‘属性id：选项id’形式组合；
3.属性类型为单选且有子属性，<b>属性值对</b>以‘属性id：选项id_子选项id’形式组合；
4.属性类型为多选，<b>属性值对</b>以‘属性id：选项id|选项id’形式组合。
<font color="red">备注</font>：属性id不能重复,也不能同prodAttributeInfoList中的属性ID相同,同一个属性ID不能有相同的选项ID*/
	private  $prodAttributeItemInfoList; 
	/**图片URL拼接字符串（以；分隔）;最多8张<br/>
<b>规格</b>：长宽都要大于380px;长宽比要大于0.98
*/
	private  $imgUrls; 
	/**合作方团购ID(不超过20个字符,等同outerId)*/
	private  $outerGroupId; 
	/**团购产品名称(不超过300个字符)*/
	private  $groupCname; 
	/**团购副标题（不超过300个字符）*/
	private  $titleDescription; 
	/**团购产品分类ID*/
	private  $groupCategoryId; 
	/**团购产品2级分类ID*/
	private  $groupChildCategoryId; 
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
	/**券有效期开始时间*/
	private  $validStartTime; 
	/**券有效期结束时间*/
	private  $validEndTime; 
	/**设施列表。多项之间使用分号分隔;每项使用冒号分隔。*/
	private  $facilities; 
	/**酒店详情(产品介绍)-接机服务、服务设施、房间设施、休闲设施、餐饮设施、会议设施、周边交通、周边信息、特色信息、设施列表*/
	private  $serviceRegulations; 
	/**特别提示(购买须知)*/
	private  $hotelNote; 
	/**好评率*/
	private  $reviewGoodRate; 
	/**预约成功率*/
	private  $reservationSuccessRate; 
	/**是否支持退款（0：否  1： 支持随时退、2：支持过期退  3： 支持随时退 & 支持过期退 ） */
	private  $refundable; 
	/**用户评论信息:
userName 用户名
score  评分
reviewDate 评论时间
reviewContent 评论内容
isTuanUser 是否团购用户*/
	private  $userReviewInfos; 
	/**门店信息（红色表示必须）:
<font color="red">cityId  城市Id</font>
districtId 行政区Id
regionId  商圈Id
<font color="red">name  商户名称</font>
<font color="red">branchName 门店名称</font>
address  地址
<font color="red">phone  预约电话1</font>
anotherPhone 预约电话2
businessHours 营业时间
longitude 经度
latitude 纬度
lonLatProvider 经纬度提供商
reviewScore 店铺评分*/
	private  $inshopDetailInfos; 
	/**酒店房型*/
	private  $hotelType; 
	/**是否钟点房(1是，0否)*/
	private  $isPartRoom; 
	/**发票提供方*/
	private  $invoiceProvider; 
	/**预约服务*/
	private  $reservationService; 
	/**产品信息（免费服务，如早餐、无线上网等）*/
	private  $productInfo; 

	public function getCategoryId(){
		return $this->categoryId;
	}
	public function getMerchantCategoryId(){
		return $this->merchantCategoryId;
	}
	public function getProductCname(){
		return $this->productCname;
	}
	public function getProductSubTitle(){
		return $this->productSubTitle;
	}
	public function getProductNamePrefix(){
		return $this->productNamePrefix;
	}
	public function getBrandId(){
		return $this->brandId;
	}
	public function getProductMarketPrice(){
		return $this->productMarketPrice;
	}
	public function getProductSalePrice(){
		return $this->productSalePrice;
	}
	public function getWeight(){
		return $this->weight;
	}
	public function getVirtualStockNum(){
		return $this->virtualStockNum;
	}
	public function getCanSale(){
		return $this->canSale;
	}
	public function getProductDescription(){
		return $this->productDescription;
	}
	public function getElectronicCerticate(){
		return $this->electronicCerticate;
	}
	public function getProdAttributeInfoList(){
		return $this->prodAttributeInfoList;
	}
	public function getProdAttributeItemInfoList(){
		return $this->prodAttributeItemInfoList;
	}
	public function getImgUrls(){
		return $this->imgUrls;
	}
	public function getOuterGroupId(){
		return $this->outerGroupId;
	}
	public function getGroupCname(){
		return $this->groupCname;
	}
	public function getTitleDescription(){
		return $this->titleDescription;
	}
	public function getGroupCategoryId(){
		return $this->groupCategoryId;
	}
	public function getGroupChildCategoryId(){
		return $this->groupChildCategoryId;
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
	public function getUserReviewInfos(){
		return $this->userReviewInfos;
	}
	public function getInshopDetailInfos(){
		return $this->inshopDetailInfos;
	}
	public function getHotelType(){
		return $this->hotelType;
	}
	public function getIsPartRoom(){
		return $this->isPartRoom;
	}
	public function getInvoiceProvider(){
		return $this->invoiceProvider;
	}
	public function getReservationService(){
		return $this->reservationService;
	}
	public function getProductInfo(){
		return $this->productInfo;
	}

	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
	}
	public function setMerchantCategoryId($merchantCategoryId){
		$this->merchantCategoryId = $merchantCategoryId;
		$this->apiParas["merchantCategoryId"] = $merchantCategoryId;
	}
	public function setProductCname($productCname){
		$this->productCname = $productCname;
		$this->apiParas["productCname"] = $productCname;
	}
	public function setProductSubTitle($productSubTitle){
		$this->productSubTitle = $productSubTitle;
		$this->apiParas["productSubTitle"] = $productSubTitle;
	}
	public function setProductNamePrefix($productNamePrefix){
		$this->productNamePrefix = $productNamePrefix;
		$this->apiParas["productNamePrefix"] = $productNamePrefix;
	}
	public function setBrandId($brandId){
		$this->brandId = $brandId;
		$this->apiParas["brandId"] = $brandId;
	}
	public function setProductMarketPrice($productMarketPrice){
		$this->productMarketPrice = $productMarketPrice;
		$this->apiParas["productMarketPrice"] = $productMarketPrice;
	}
	public function setProductSalePrice($productSalePrice){
		$this->productSalePrice = $productSalePrice;
		$this->apiParas["productSalePrice"] = $productSalePrice;
	}
	public function setWeight($weight){
		$this->weight = $weight;
		$this->apiParas["weight"] = $weight;
	}
	public function setVirtualStockNum($virtualStockNum){
		$this->virtualStockNum = $virtualStockNum;
		$this->apiParas["virtualStockNum"] = $virtualStockNum;
	}
	public function setCanSale($canSale){
		$this->canSale = $canSale;
		$this->apiParas["canSale"] = $canSale;
	}
	public function setProductDescription($productDescription){
		$this->productDescription = $productDescription;
		$this->apiParas["productDescription"] = $productDescription;
	}
	public function setElectronicCerticate($electronicCerticate){
		$this->electronicCerticate = $electronicCerticate;
		$this->apiParas["electronicCerticate"] = $electronicCerticate;
	}
	public function setProdAttributeInfoList($prodAttributeInfoList){
		$this->prodAttributeInfoList = $prodAttributeInfoList;
		$this->apiParas["prodAttributeInfoList"] = $prodAttributeInfoList;
	}
	public function setProdAttributeItemInfoList($prodAttributeItemInfoList){
		$this->prodAttributeItemInfoList = $prodAttributeItemInfoList;
		$this->apiParas["prodAttributeItemInfoList"] = $prodAttributeItemInfoList;
	}
	public function setImgUrls($imgUrls){
		$this->imgUrls = $imgUrls;
		$this->apiParas["imgUrls"] = $imgUrls;
	}
	public function setOuterGroupId($outerGroupId){
		$this->outerGroupId = $outerGroupId;
		$this->apiParas["outerGroupId"] = $outerGroupId;
	}
	public function setGroupCname($groupCname){
		$this->groupCname = $groupCname;
		$this->apiParas["groupCname"] = $groupCname;
	}
	public function setTitleDescription($titleDescription){
		$this->titleDescription = $titleDescription;
		$this->apiParas["titleDescription"] = $titleDescription;
	}
	public function setGroupCategoryId($groupCategoryId){
		$this->groupCategoryId = $groupCategoryId;
		$this->apiParas["groupCategoryId"] = $groupCategoryId;
	}
	public function setGroupChildCategoryId($groupChildCategoryId){
		$this->groupChildCategoryId = $groupChildCategoryId;
		$this->apiParas["groupChildCategoryId"] = $groupChildCategoryId;
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
	public function setUserReviewInfos($userReviewInfos){
		$this->userReviewInfos = $userReviewInfos;
		$this->apiParas["userReviewInfos"] = $userReviewInfos;
	}
	public function setInshopDetailInfos($inshopDetailInfos){
		$this->inshopDetailInfos = $inshopDetailInfos;
		$this->apiParas["inshopDetailInfos"] = $inshopDetailInfos;
	}
	public function setHotelType($hotelType){
		$this->hotelType = $hotelType;
		$this->apiParas["hotelType"] = $hotelType;
	}
	public function setIsPartRoom($isPartRoom){
		$this->isPartRoom = $isPartRoom;
		$this->apiParas["isPartRoom"] = $isPartRoom;
	}
	public function setInvoiceProvider($invoiceProvider){
		$this->invoiceProvider = $invoiceProvider;
		$this->apiParas["invoiceProvider"] = $invoiceProvider;
	}
	public function setReservationService($reservationService){
		$this->reservationService = $reservationService;
		$this->apiParas["reservationService"] = $reservationService;
	}
	public function setProductInfo($productInfo){
		$this->productInfo = $productInfo;
		$this->apiParas["productInfo"] = $productInfo;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
