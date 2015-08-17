<?php
/**
*团购报名信息上传
*/

class GroupBuyProductAddRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.group.buy.product.add";
	}
	
	/**1号店产品ID*/
	private  $productId; 
	/**合作方团购ID(不超过20个字符)*/
	private  $outerGroupId; 
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

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterGroupId(){
		return $this->outerGroupId;
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

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setOuterGroupId($outerGroupId){
		$this->outerGroupId = $outerGroupId;
		$this->apiParas["outerGroupId"] = $outerGroupId;
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

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
