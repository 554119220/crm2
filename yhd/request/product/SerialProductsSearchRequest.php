<?php
/**
*查询系列产品信息
*/

class SerialProductsSearchRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.serial.products.search";
	}
	
	/**是否可见(强制上/下架),1是0否*/
	private  $canShow; 
	/**上下架状态0：下架，1：上架*/
	private  $canSale; 
	/**产品审核状态:1.未审核;2.审核通过;3.审核失败*/
	private  $verifyFlg; 
	/**1号店产品ID列表(逗号分隔,优先于outerIdList),最多100个*/
	private  $productIdList; 
	/**外部产品编码列表(逗号分隔),最多100个*/
	private  $outerIdList; 
	/**产品中文名称（支持模糊查询）*/
	private  $productCname; 
	/**当前页数(默认1)*/
	private  $curPage; 
	/**每页显示记录数(默认50、最大限制：100)*/
	private  $pageRows; 
	/**产品类别Id*/
	private  $categoryId; 
	/**产品类别类型（0:1号店类别,1:商家自定义类别,默认为0）*/
	private  $categoryType; 
	/**品牌Id*/
	private  $brandId; 
	/**产品编码列表（逗号分隔）与productIdList、outerIdList三选一,最大长度为100，优先级最低*/
	private  $productCodeList; 

	public function getCanShow(){
		return $this->canShow;
	}
	public function getCanSale(){
		return $this->canSale;
	}
	public function getVerifyFlg(){
		return $this->verifyFlg;
	}
	public function getProductIdList(){
		return $this->productIdList;
	}
	public function getOuterIdList(){
		return $this->outerIdList;
	}
	public function getProductCname(){
		return $this->productCname;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getCategoryId(){
		return $this->categoryId;
	}
	public function getCategoryType(){
		return $this->categoryType;
	}
	public function getBrandId(){
		return $this->brandId;
	}
	public function getProductCodeList(){
		return $this->productCodeList;
	}

	public function setCanShow($canShow){
		$this->canShow = $canShow;
		$this->apiParas["canShow"] = $canShow;
	}
	public function setCanSale($canSale){
		$this->canSale = $canSale;
		$this->apiParas["canSale"] = $canSale;
	}
	public function setVerifyFlg($verifyFlg){
		$this->verifyFlg = $verifyFlg;
		$this->apiParas["verifyFlg"] = $verifyFlg;
	}
	public function setProductIdList($productIdList){
		$this->productIdList = $productIdList;
		$this->apiParas["productIdList"] = $productIdList;
	}
	public function setOuterIdList($outerIdList){
		$this->outerIdList = $outerIdList;
		$this->apiParas["outerIdList"] = $outerIdList;
	}
	public function setProductCname($productCname){
		$this->productCname = $productCname;
		$this->apiParas["productCname"] = $productCname;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
	}
	public function setCategoryType($categoryType){
		$this->categoryType = $categoryType;
		$this->apiParas["categoryType"] = $categoryType;
	}
	public function setBrandId($brandId){
		$this->brandId = $brandId;
		$this->apiParas["brandId"] = $brandId;
	}
	public function setProductCodeList($productCodeList){
		$this->productCodeList = $productCodeList;
		$this->apiParas["productCodeList"] = $productCodeList;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
