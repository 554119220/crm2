<?php
/**
*新增一个产品(单品)
*/

class ProductAddRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.product.add";
	}
	
	/**产品类型(0普通产品;1图书)*/
	private  $productType; 
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
	/**外部产品编码(不超过30个字符)*/
	private  $outerId; 
	/**市场价(最多两位小数)*/
	private  $productMarketPrice; 
	/**销售价(不能大于市场价,最多两位小数)*/
	private  $productSalePrice; 
	/**节能补贴金额(图书产品不支持,不能大于市场价,最多两位小数,50~400)*/
	private  $subsidyAmount; 
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
	/**标题(图书独有属性)*/
	private  $bookTitle; 
	/**出版社推荐语(图书独有属性)*/
	private  $recommended; 
	/**作者简介(图书独有属性)*/
	private  $authorIntroduction; 
	/**目录(图书独有属性)*/
	private  $catalog; 
	/**书摘(图书独有属性)*/
	private  $digest; 
	/**内容简介(图书独有属性)*/
	private  $contentIntroduction; 
	/**媒体报道(图书独有属性)*/
	private  $mediaReport; 
	/**图片 Urls*/
	private  $imgUrls; 

	public function getProductType(){
		return $this->productType;
	}
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
	public function getOuterId(){
		return $this->outerId;
	}
	public function getProductMarketPrice(){
		return $this->productMarketPrice;
	}
	public function getProductSalePrice(){
		return $this->productSalePrice;
	}
	public function getSubsidyAmount(){
		return $this->subsidyAmount;
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
	public function getBookTitle(){
		return $this->bookTitle;
	}
	public function getRecommended(){
		return $this->recommended;
	}
	public function getAuthorIntroduction(){
		return $this->authorIntroduction;
	}
	public function getCatalog(){
		return $this->catalog;
	}
	public function getDigest(){
		return $this->digest;
	}
	public function getContentIntroduction(){
		return $this->contentIntroduction;
	}
	public function getMediaReport(){
		return $this->mediaReport;
	}
	public function getImgUrls(){
		return $this->imgUrls;
	}

	public function setProductType($productType){
		$this->productType = $productType;
		$this->apiParas["productType"] = $productType;
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
	public function setOuterId($outerId){
		$this->outerId = $outerId;
		$this->apiParas["outerId"] = $outerId;
	}
	public function setProductMarketPrice($productMarketPrice){
		$this->productMarketPrice = $productMarketPrice;
		$this->apiParas["productMarketPrice"] = $productMarketPrice;
	}
	public function setProductSalePrice($productSalePrice){
		$this->productSalePrice = $productSalePrice;
		$this->apiParas["productSalePrice"] = $productSalePrice;
	}
	public function setSubsidyAmount($subsidyAmount){
		$this->subsidyAmount = $subsidyAmount;
		$this->apiParas["subsidyAmount"] = $subsidyAmount;
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
	public function setBookTitle($bookTitle){
		$this->bookTitle = $bookTitle;
		$this->apiParas["bookTitle"] = $bookTitle;
	}
	public function setRecommended($recommended){
		$this->recommended = $recommended;
		$this->apiParas["recommended"] = $recommended;
	}
	public function setAuthorIntroduction($authorIntroduction){
		$this->authorIntroduction = $authorIntroduction;
		$this->apiParas["authorIntroduction"] = $authorIntroduction;
	}
	public function setCatalog($catalog){
		$this->catalog = $catalog;
		$this->apiParas["catalog"] = $catalog;
	}
	public function setDigest($digest){
		$this->digest = $digest;
		$this->apiParas["digest"] = $digest;
	}
	public function setContentIntroduction($contentIntroduction){
		$this->contentIntroduction = $contentIntroduction;
		$this->apiParas["contentIntroduction"] = $contentIntroduction;
	}
	public function setMediaReport($mediaReport){
		$this->mediaReport = $mediaReport;
		$this->apiParas["mediaReport"] = $mediaReport;
	}
	public function setImgUrls($imgUrls){
		$this->imgUrls = $imgUrls;
		$this->apiParas["imgUrls"] = $imgUrls;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
