<?php
/**
*单个检验新品提报信息
*/

class SupplierProductValidateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.product.validate";
	}
	
	/**供应商名称*/
	private  $supplierCompanyName; 
	/**类别叶子节点名称*/
	private  $categoryName; 
	/**品牌名称*/
	private  $brandName; 
	/**商品名称*/
	private  $productCname; 
	/**销售价格（元）*/
	private  $salePrice; 
	/**市场价（元）*/
	private  $productListPrice; 
	/**进项税率（百分比）*/
	private  $inTaxRate; 
	/**销售税率（百分比）*/
	private  $salesTax; 
	/**销售区域*/
	private  $merchantName; 
	/**商品毛重（公斤）*/
	private  $grossWeight; 
	/**商品长度（厘米）*/
	private  $length; 
	/**商品宽度（厘米）*/
	private  $width; 
	/**商品高度（厘米）*/
	private  $height; 
	/**商品箱规*/
	private  $stdPackQty; 
	/**商品条码*/
	private  $ean13; 
	/**物流码*/
	private  $logisticsNum; 
	/**颜色*/
	private  $color; 
	/**尺寸*/
	private  $productSize; 
	/**产地*/
	private  $placeOfOrigin; 
	/**保质期天数*/
	private  $productQualityAssuranceDay; 
	/**样品返还地址*/
	private  $sampleReturnAddress; 
	/**最低进价或最低限价（元）*/
	private  $inPrice; 
	/**商品副标题*/
	private  $nameSubtitle; 
	/**是否是赠品*/
	private  $productIsGift; 
	/**是否进口*/
	private  $isImported; 
	/**是否有保质期*/
	private  $userExpireControl; 
	/**是否3c*/
	private  $isccc; 
	/**是否需要序列号控制*/
	private  $needSnControl; 
	/**文描是否外包*/
	private  $isOutsourcing; 
	/**是否需要返还样品*/
	private  $isSampleNeedReturn; 

	public function getSupplierCompanyName(){
		return $this->supplierCompanyName;
	}
	public function getCategoryName(){
		return $this->categoryName;
	}
	public function getBrandName(){
		return $this->brandName;
	}
	public function getProductCname(){
		return $this->productCname;
	}
	public function getSalePrice(){
		return $this->salePrice;
	}
	public function getProductListPrice(){
		return $this->productListPrice;
	}
	public function getInTaxRate(){
		return $this->inTaxRate;
	}
	public function getSalesTax(){
		return $this->salesTax;
	}
	public function getMerchantName(){
		return $this->merchantName;
	}
	public function getGrossWeight(){
		return $this->grossWeight;
	}
	public function getLength(){
		return $this->length;
	}
	public function getWidth(){
		return $this->width;
	}
	public function getHeight(){
		return $this->height;
	}
	public function getStdPackQty(){
		return $this->stdPackQty;
	}
	public function getEan13(){
		return $this->ean13;
	}
	public function getLogisticsNum(){
		return $this->logisticsNum;
	}
	public function getColor(){
		return $this->color;
	}
	public function getProductSize(){
		return $this->productSize;
	}
	public function getPlaceOfOrigin(){
		return $this->placeOfOrigin;
	}
	public function getProductQualityAssuranceDay(){
		return $this->productQualityAssuranceDay;
	}
	public function getSampleReturnAddress(){
		return $this->sampleReturnAddress;
	}
	public function getInPrice(){
		return $this->inPrice;
	}
	public function getNameSubtitle(){
		return $this->nameSubtitle;
	}
	public function getProductIsGift(){
		return $this->productIsGift;
	}
	public function getIsImported(){
		return $this->isImported;
	}
	public function getUserExpireControl(){
		return $this->userExpireControl;
	}
	public function getIsccc(){
		return $this->isccc;
	}
	public function getNeedSnControl(){
		return $this->needSnControl;
	}
	public function getIsOutsourcing(){
		return $this->isOutsourcing;
	}
	public function getIsSampleNeedReturn(){
		return $this->isSampleNeedReturn;
	}

	public function setSupplierCompanyName($supplierCompanyName){
		$this->supplierCompanyName = $supplierCompanyName;
		$this->apiParas["supplierCompanyName"] = $supplierCompanyName;
	}
	public function setCategoryName($categoryName){
		$this->categoryName = $categoryName;
		$this->apiParas["categoryName"] = $categoryName;
	}
	public function setBrandName($brandName){
		$this->brandName = $brandName;
		$this->apiParas["brandName"] = $brandName;
	}
	public function setProductCname($productCname){
		$this->productCname = $productCname;
		$this->apiParas["productCname"] = $productCname;
	}
	public function setSalePrice($salePrice){
		$this->salePrice = $salePrice;
		$this->apiParas["salePrice"] = $salePrice;
	}
	public function setProductListPrice($productListPrice){
		$this->productListPrice = $productListPrice;
		$this->apiParas["productListPrice"] = $productListPrice;
	}
	public function setInTaxRate($inTaxRate){
		$this->inTaxRate = $inTaxRate;
		$this->apiParas["inTaxRate"] = $inTaxRate;
	}
	public function setSalesTax($salesTax){
		$this->salesTax = $salesTax;
		$this->apiParas["salesTax"] = $salesTax;
	}
	public function setMerchantName($merchantName){
		$this->merchantName = $merchantName;
		$this->apiParas["merchantName"] = $merchantName;
	}
	public function setGrossWeight($grossWeight){
		$this->grossWeight = $grossWeight;
		$this->apiParas["grossWeight"] = $grossWeight;
	}
	public function setLength($length){
		$this->length = $length;
		$this->apiParas["length"] = $length;
	}
	public function setWidth($width){
		$this->width = $width;
		$this->apiParas["width"] = $width;
	}
	public function setHeight($height){
		$this->height = $height;
		$this->apiParas["height"] = $height;
	}
	public function setStdPackQty($stdPackQty){
		$this->stdPackQty = $stdPackQty;
		$this->apiParas["stdPackQty"] = $stdPackQty;
	}
	public function setEan13($ean13){
		$this->ean13 = $ean13;
		$this->apiParas["ean13"] = $ean13;
	}
	public function setLogisticsNum($logisticsNum){
		$this->logisticsNum = $logisticsNum;
		$this->apiParas["logisticsNum"] = $logisticsNum;
	}
	public function setColor($color){
		$this->color = $color;
		$this->apiParas["color"] = $color;
	}
	public function setProductSize($productSize){
		$this->productSize = $productSize;
		$this->apiParas["productSize"] = $productSize;
	}
	public function setPlaceOfOrigin($placeOfOrigin){
		$this->placeOfOrigin = $placeOfOrigin;
		$this->apiParas["placeOfOrigin"] = $placeOfOrigin;
	}
	public function setProductQualityAssuranceDay($productQualityAssuranceDay){
		$this->productQualityAssuranceDay = $productQualityAssuranceDay;
		$this->apiParas["productQualityAssuranceDay"] = $productQualityAssuranceDay;
	}
	public function setSampleReturnAddress($sampleReturnAddress){
		$this->sampleReturnAddress = $sampleReturnAddress;
		$this->apiParas["sampleReturnAddress"] = $sampleReturnAddress;
	}
	public function setInPrice($inPrice){
		$this->inPrice = $inPrice;
		$this->apiParas["inPrice"] = $inPrice;
	}
	public function setNameSubtitle($nameSubtitle){
		$this->nameSubtitle = $nameSubtitle;
		$this->apiParas["nameSubtitle"] = $nameSubtitle;
	}
	public function setProductIsGift($productIsGift){
		$this->productIsGift = $productIsGift;
		$this->apiParas["productIsGift"] = $productIsGift;
	}
	public function setIsImported($isImported){
		$this->isImported = $isImported;
		$this->apiParas["isImported"] = $isImported;
	}
	public function setUserExpireControl($userExpireControl){
		$this->userExpireControl = $userExpireControl;
		$this->apiParas["userExpireControl"] = $userExpireControl;
	}
	public function setIsccc($isccc){
		$this->isccc = $isccc;
		$this->apiParas["isccc"] = $isccc;
	}
	public function setNeedSnControl($needSnControl){
		$this->needSnControl = $needSnControl;
		$this->apiParas["needSnControl"] = $needSnControl;
	}
	public function setIsOutsourcing($isOutsourcing){
		$this->isOutsourcing = $isOutsourcing;
		$this->apiParas["isOutsourcing"] = $isOutsourcing;
	}
	public function setIsSampleNeedReturn($isSampleNeedReturn){
		$this->isSampleNeedReturn = $isSampleNeedReturn;
		$this->apiParas["isSampleNeedReturn"] = $isSampleNeedReturn;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
