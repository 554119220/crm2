<?php
/**
*根据关键字或类目ID查询商品
*/

class ProductSearchBykeyRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.product.search.bykey";
	}
	
	/**商品标题中包含的关键字. 注意:查询时keyword,cid至少选择其中一个参数*/
	private  $keyword; 
	/**标准商品后台类目id。该ID可以通过yhd.itemcats.get接口获取到。 注意:keyword,cid至少选择其中一个参数*/
	private  $cid; 
	/**商品所在地，省、直辖市名称*/
	private  $area; 
	/**结果当前页数*/
	private  $pageNo; 
	/**每页返回结果数*/
	private  $pageSize; 

	public function getKeyword(){
		return $this->keyword;
	}
	public function getCid(){
		return $this->cid;
	}
	public function getArea(){
		return $this->area;
	}
	public function getPageNo(){
		return $this->pageNo;
	}
	public function getPageSize(){
		return $this->pageSize;
	}

	public function setKeyword($keyword){
		$this->keyword = $keyword;
		$this->apiParas["keyword"] = $keyword;
	}
	public function setCid($cid){
		$this->cid = $cid;
		$this->apiParas["cid"] = $cid;
	}
	public function setArea($area){
		$this->area = $area;
		$this->apiParas["area"] = $area;
	}
	public function setPageNo($pageNo){
		$this->pageNo = $pageNo;
		$this->apiParas["pageNo"] = $pageNo;
	}
	public function setPageSize($pageSize){
		$this->pageSize = $pageSize;
		$this->apiParas["pageSize"] = $pageSize;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
