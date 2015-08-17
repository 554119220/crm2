<?php
/**
*更新一个产品
*/

class ProductUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.product.update";
	}
	
	/**1号店产品ID,与outerId二选一(productId优先)*/
	private  $productId; 
	/**外部产品ID,与productId二选一*/
	private  $outerId; 
	/**产品类别ID(只有单品能修改,从获取授权产品类别yhd.category.products.get接口中获取叶子ID)*/
	private  $categoryId; 
	/**商家产品类别ID列表(从获取商家产品类别接口中获取叶子ID)*/
	private  $merchantCategoryId; 
	/**产品名称(需要在产品名称前面添加品牌名称加空格,不超过100个字符)*/
	private  $productCname; 
	/**产品名称副标题(不超过100个字符)*/
	private  $productSubTitle; 
	/**产品名称前缀(不超过10个字符)*/
	private  $productNamePrefix; 
	/**产品描述(文描)(不超过300kb)*/
	private  $productDescription; 
	/**节能补贴金额(最多两位小数,50~400)*/
	private  $subsidyAmount; 
	/**产品属性列表(每个属性之间用逗号分隔,属性ID和属性值之间用冒号分隔,其中属性值可以不输入。如属性ID:属性值),属性id不能重复*/
	private  $prodAttributeInfoList; 
	/**属性项列表:
1.每个<b>属性值对</b>以逗号分隔（<b>属性值对</b>：由属性id、选项id、子选项id组成）；
2.属性类型为单选且没有子属性，<b>属性值对</b>以‘属性id：选项id’形式组合；
3.属性类型为单选且有子属性，<b>属性值对</b>以‘属性id：选项id_子选项id’形式组合；
4.属性类型为多选，<b>属性值对</b>以‘属性id：选项id|选项id’形式组合。
<font color="red">备注</font>：属性id不能重复,也不能同prodAttributeInfoList中的属性ID相同,同一个属性ID不能有相同的选项ID*/
	private  $prodAttributeItemInfoList; 

	public function getProductId(){
		return $this->productId;
	}
	public function getOuterId(){
		return $this->outerId;
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
	public function getProductDescription(){
		return $this->productDescription;
	}
	public function getSubsidyAmount(){
		return $this->subsidyAmount;
	}
	public function getProdAttributeInfoList(){
		return $this->prodAttributeInfoList;
	}
	public function getProdAttributeItemInfoList(){
		return $this->prodAttributeItemInfoList;
	}

	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setOuterId($outerId){
		$this->outerId = $outerId;
		$this->apiParas["outerId"] = $outerId;
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
	public function setProductDescription($productDescription){
		$this->productDescription = $productDescription;
		$this->apiParas["productDescription"] = $productDescription;
	}
	public function setSubsidyAmount($subsidyAmount){
		$this->subsidyAmount = $subsidyAmount;
		$this->apiParas["subsidyAmount"] = $subsidyAmount;
	}
	public function setProdAttributeInfoList($prodAttributeInfoList){
		$this->prodAttributeInfoList = $prodAttributeInfoList;
		$this->apiParas["prodAttributeInfoList"] = $prodAttributeInfoList;
	}
	public function setProdAttributeItemInfoList($prodAttributeItemInfoList){
		$this->prodAttributeItemInfoList = $prodAttributeItemInfoList;
		$this->apiParas["prodAttributeItemInfoList"] = $prodAttributeItemInfoList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
