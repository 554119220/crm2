<?php
/**
*添加一个新商品
*/

class SupplierProductAddRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.product.add";
	}
	
	/**供应商名称*/
	private  $supplierCompanyName; 
	/**类别叶子节点名称。如果缺失类别信息，可使用商品类别查询工具，取出最后一级的子类别，禁止私自填写类别名称。商品类目需要先进行资质审核，如果你需要在新的类目上提报商品，请联系我们的采购经理，添加新的类目数据*/
	private  $categoryName; 
	/**品牌名称。如果缺失品牌信息，可以用品牌查询工具。禁止私自随意填写品牌名称。品牌需要先进行资质审核，如果你需要提交新品牌商品，请联系我们的采购经理，添加新的品牌数据*/
	private  $brandName; 
	/**商品名称。不允许出现标点符号等特殊字符，可用空格替代。*/
	private  $productCname; 
	/**商品副标题*/
	private  $nameSubtitle; 
	/**销售价格*/
	private  $salePrice; 
	/**最低进价或最低限价*/
	private  $inPrice; 
	/**市场价*/
	private  $productListPrice; 
	/**进项税率。填入小数数字，如0.13代表百分之13。此项只能填入0.00、0.03、0.13或0.17，其他值均不被接受*/
	private  $inTaxRate; 
	/**销售税率。填入小数数字，如0.13代表百分之13。此项只能填入0.00、0.13或0.17，其他值均不被接受*/
	private  $salesTax; 
	/**是否是赠品*/
	private  $productIsGift; 
	/**销售区域*/
	private  $merchantName; 
	/**商品毛重（千克），不能为0*/
	private  $grossWeight; 
	/**商品长度（厘米），不能为0*/
	private  $length; 
	/**商品宽度（厘米），不能为0*/
	private  $width; 
	/**商品高度（厘米），不能为0*/
	private  $height; 
	/**商品箱规。即同一个包装箱内商品的数量*/
	private  $stdPackQty; 
	/**商品条码。请使用13位国际标准条码*/
	private  $ean13; 
	/**物流码。是指需要贴码的入库商品的贴码编号*/
	private  $logisticsNum; 
	/**颜色*/
	private  $color; 
	/**尺寸（任意单位）*/
	private  $productSize; 
	/**是否进口*/
	private  $isImported; 
	/**产地*/
	private  $placeOfOrigin; 
	/**是否有保质期*/
	private  $userExpireControl; 
	/**保质期天数*/
	private  $productQualityAssuranceDay; 
	/**是否是3C类产品*/
	private  $isccc; 
	/**是否需要序列号控制*/
	private  $needSnControl; 
	/**文描是否外包*/
	private  $isOutsourcing; 
	/**是否需要返还样品*/
	private  $isSampleNeedReturn; 
	/**样品返还地址*/
	private  $sampleReturnAddress; 

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
	public function getNameSubtitle(){
		return $this->nameSubtitle;
	}
	public function getSalePrice(){
		return $this->salePrice;
	}
	public function getInPrice(){
		return $this->inPrice;
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
	public function getProductIsGift(){
		return $this->productIsGift;
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
	public function getIsImported(){
		return $this->isImported;
	}
	public function getPlaceOfOrigin(){
		return $this->placeOfOrigin;
	}
	public function getUserExpireControl(){
		return $this->userExpireControl;
	}
	public function getProductQualityAssuranceDay(){
		return $this->productQualityAssuranceDay;
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
	public function getSampleReturnAddress(){
		return $this->sampleReturnAddress;
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
	public function setNameSubtitle($nameSubtitle){
		$this->nameSubtitle = $nameSubtitle;
		$this->apiParas["nameSubtitle"] = $nameSubtitle;
	}
	public function setSalePrice($salePrice){
		$this->salePrice = $salePrice;
		$this->apiParas["salePrice"] = $salePrice;
	}
	public function setInPrice($inPrice){
		$this->inPrice = $inPrice;
		$this->apiParas["inPrice"] = $inPrice;
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
	public function setProductIsGift($productIsGift){
		$this->productIsGift = $productIsGift;
		$this->apiParas["productIsGift"] = $productIsGift;
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
	public function setIsImported($isImported){
		$this->isImported = $isImported;
		$this->apiParas["isImported"] = $isImported;
	}
	public function setPlaceOfOrigin($placeOfOrigin){
		$this->placeOfOrigin = $placeOfOrigin;
		$this->apiParas["placeOfOrigin"] = $placeOfOrigin;
	}
	public function setUserExpireControl($userExpireControl){
		$this->userExpireControl = $userExpireControl;
		$this->apiParas["userExpireControl"] = $userExpireControl;
	}
	public function setProductQualityAssuranceDay($productQualityAssuranceDay){
		$this->productQualityAssuranceDay = $productQualityAssuranceDay;
		$this->apiParas["productQualityAssuranceDay"] = $productQualityAssuranceDay;
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
	public function setSampleReturnAddress($sampleReturnAddress){
		$this->sampleReturnAddress = $sampleReturnAddress;
		$this->apiParas["sampleReturnAddress"] = $sampleReturnAddress;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
