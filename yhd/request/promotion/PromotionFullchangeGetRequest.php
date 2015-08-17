<?php
/**
*查找满就换购列表促销
*/

class PromotionFullchangeGetRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.promotion.fullchange.get";
	}
	
	/**促销开始时间，格式如示例，开始时间*/
	private  $startDate; 
	/**状态值  -1:所有 0:已取消 1:尚未生效 2:生效中 3:已过期 */
	private  $status; 
	/**分页查询第几页*/
	private  $curPage; 
	/**分页查询每页的个数*/
	private  $pageRows; 
	/**查询的产品类别id(产品名称、id和类别，筛选的都是按商品添加的促销)*/
	private  $categoryId; 
	/**查询的产品名称，模糊查询  */
	private  $productCname; 
	/**促销结束时间，格式如示例*/
	private  $endDate; 

	public function getStartDate(){
		return $this->startDate;
	}
	public function getStatus(){
		return $this->status;
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
	public function getProductCname(){
		return $this->productCname;
	}
	public function getEndDate(){
		return $this->endDate;
	}

	public function setStartDate($startDate){
		$this->startDate = $startDate;
		$this->apiParas["startDate"] = $startDate;
	}
	public function setStatus($status){
		$this->status = $status;
		$this->apiParas["status"] = $status;
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
	public function setProductCname($productCname){
		$this->productCname = $productCname;
		$this->apiParas["productCname"] = $productCname;
	}
	public function setEndDate($endDate){
		$this->endDate = $endDate;
		$this->apiParas["endDate"] = $endDate;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
