<?php
/**
*查询套餐产品信息
*/

class CombineProductsSearchRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.combine.products.search";
	}
	
	/**是否可见(强制上/下架),1是0否*/
	private  $canShow; 
	/**上下架状态0：下架，1：上架*/
	private  $canSale; 
	/**当前页数（默认1）*/
	private  $curPage; 
	/**每页显示记录数（默认50、最大限制：100）*/
	private  $pageRows; 

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

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
