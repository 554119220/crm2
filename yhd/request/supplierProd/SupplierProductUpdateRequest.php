<?php
/**
*更新供应商产品信息
*/

class SupplierProductUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.product.update";
	}
	
	/**1号店产品ID*/
	private  $productId; 
	/**产品类别ID(只有单品能修改）*/
	private  $categoryId; 
	/**产品名称(需要在产品名称前面添加品牌名称加空格,不超过100个字符)*/
	private  $productCname; 
	/**产品名称副标题(不超过100个字符)*/
	private  $productSubTitle; 
	/**产品名称前缀(不超过10个字符)*/
	private  $productNamePrefix; 
	/**产品描述(文描)(不超过300kb)*/
	private  $productDescription; 
	/**产品属性列表(每个属性之间用逗号分隔,属性ID和属性值之间用冒号分隔,其中属性值可以不输入。如属性ID:属性值),属性id不能重复*/
	private  $prodAttributeInfoList; 
	/**属性项列表(每个属性之间用逗号分隔,属性ID和选项ID之间用冒号分隔,选项ID之间用竖线分隔,其中选项ID可以不输入。如属性ID:选项ID1|选项ID2),属性id不能重复,也不能同上面的属性ID相同,同一个属性ID不能有相同的选项ID*/
	private  $prodAttributeItemInfoList; 

	public function getProductId(){
		return $this->productId;
	}
	public function getCategoryId(){
		return $this->categoryId;
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
	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
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
