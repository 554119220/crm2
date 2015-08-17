<?php
/**
*获取持券商家列表接口
*/

class PromotionUnitecouponsMerchantlistGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.unitecoupons.merchantlist.get";
	}
	
	/**商家店铺名称模糊查询*/
	private  $merchantCompanyName; 
	/**当前页码*/
	private  $curPage; 
	/**每页数量*/
	private  $pageRows; 

	public function getMerchantCompanyName(){
		return $this->merchantCompanyName;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}

	public function setMerchantCompanyName($merchantCompanyName){
		$this->merchantCompanyName = $merchantCompanyName;
		$this->apiParas["merchantCompanyName"] = $merchantCompanyName;
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
