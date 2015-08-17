<?php
/**
*查询价格促销列表
*/

class PromotionPriceGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.price.get";
	}
	
	/**查询的起始时间*/
	private  $startDate; 
	/**查询的结束时间*/
	private  $endDate; 
	/**1：未生效  2：生效中   3：未生效+生效中*/
	private  $status; 
	/**分页查询每页的个数*/
	private  $pageRows; 
	/**分页查询第几页*/
	private  $curPage; 
	/**查询的产品id,只能查普通产品和虚品。*/
	private  $productId; 
	/**查询的产品名称，模糊查询*/
	private  $productCname; 
	/**查询的类别id*/
	private  $categoryId; 

	public function getStartDate(){
		return $this->startDate;
	}
	public function getEndDate(){
		return $this->endDate;
	}
	public function getStatus(){
		return $this->status;
	}
	public function getPageRows(){
		return $this->pageRows;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getProductId(){
		return $this->productId;
	}
	public function getProductCname(){
		return $this->productCname;
	}
	public function getCategoryId(){
		return $this->categoryId;
	}

	public function setStartDate($startDate){
		$this->startDate = $startDate;
		$this->apiParas["startDate"] = $startDate;
	}
	public function setEndDate($endDate){
		$this->endDate = $endDate;
		$this->apiParas["endDate"] = $endDate;
	}
	public function setStatus($status){
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}
	public function setPageRows($pageRows){
		$this->pageRows = $pageRows;
		$this->apiParas["pageRows"] = $pageRows;
	}
	public function setCurPage($curPage){
		$this->curPage = $curPage;
		$this->apiParas["curPage"] = $curPage;
	}
	public function setProductId($productId){
		$this->productId = $productId;
		$this->apiParas["productId"] = $productId;
	}
	public function setProductCname($productCname){
		$this->productCname = $productCname;
		$this->apiParas["productCname"] = $productCname;
	}
	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
