<?php
/**
*查询供应商产品信息
*/

class SupplierProductsGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.supplier.products.get";
	}
	
	/**是否可见(强制上/下架),1是0否*/
	private  $canShow; 
	/**上下架状态0：下架，1：上架*/
	private  $canSale; 
	/**当前页数（默认1）*/
	private  $curPage; 
	/**每页显示记录数（默认50、最大限制：100）*/
	private  $pageRows; 
	/**商家产品中文名称(支持模糊查询)*/
	private  $productCname; 
	/**产品Id列表(最多productId个数为100)*/
	private  $productIdList; 
	/**产品类别Id（1号店类别）*/
	private  $categoryId; 
	/**品牌Id*/
	private  $brandId; 
	/**产品类型  0：普通产品 1：主系列产品 2：子系列产品 3：捆绑产品 4：实体礼品卡 5: 虚拟商品 6:增值服务 7:电子礼品卡*/
	private  $productType; 

	public function getCanShow(){
		return $this->canShow;
	}
	public function getCanSale(){
		return $this->canSale;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getProductCname(){
		return $this->productCname;
	}
	public function getProductIdList(){
		return $this->productIdList;
	}
	public function getCategoryId(){
		return $this->categoryId;
	}
	public function getBrandId(){
		return $this->brandId;
	}
	public function getProductType(){
		return $this->productType;
	}

	public function setCanShow($canShow){
		$this->canShow = $canShow;
		$this->apiParas["canShow"] = $canShow;
	}
	public function setCanSale($canSale){
		$this->canSale = $canSale;
		$this->apiParas["canSale"] = $canSale;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setProductCname($productCname){
		$this->productCname = $productCname;
		$this->apiParas["productCname"] = $productCname;
	}
	public function setProductIdList($productIdList){
		$this->productIdList = $productIdList;
		$this->apiParas["productIdList"] = $productIdList;
	}
	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
	}
	public function setBrandId($brandId){
		$this->brandId = $brandId;
		$this->apiParas["brandId"] = $brandId;
	}
	public function setProductType($productType){
		$this->productType = $productType;
		$this->apiParas["productType"] = $productType;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
