<?php
/**
*查询网盟团购信息
*/

class UnionGrouponGetRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.union.groupon.get";
	}
	
	/**联盟用户id*/
	private  $trackerU; 
	/**网站id，预留字段，暂时不使用*/
	private  $websiteId; 
	/**用户id，预留字段，暂时不使用*/
	private  $uid; 
	/**省份id*/
	private  $provinceId; 
	/**模糊查询名称*/
	private  $keyword; 
	/**团购商品所属类目*/
	private  $categoryId; 
	/**站点，1：1号店自营商品，2：1号店商城商品*/
	private  $siteType; 
	/**折后价区间价格最小值*/
	private  $discountPriceMin; 
	/**折后价区间价格最大值*/
	private  $discountPriceMax; 
	/**店铺佣金比例查询最小值。单位：万分之（如：填20，代表0.02%）*/
	private  $commissionRateMin; 
	/**店铺佣金比例查询最大值。单位：万分之（如：填30，代表0.03%）*/
	private  $commissionRateMax; 
	/**排序字段。commision_rate：佣金率。groupon_id：团购ID；people_number：参团人数；discount_price：折后价格*/
	private  $sort; 
	/**排序方式。desc：降序排列；asc：升序排列*/
	private  $sortType; 
	/**页码。结果页1-99（默认为1）*/
	private  $curPage; 
	/**每页最大条数（每页显示记录数，默认50，最大50）*/
	private  $pageRows; 

	public function getTrackerU(){
		return $this->trackerU;
	}
	public function getWebsiteId(){
		return $this->websiteId;
	}
	public function getUid(){
		return $this->uid;
	}
	public function getProvinceId(){
		return $this->provinceId;
	}
	public function getKeyword(){
		return $this->keyword;
	}
	public function getCategoryId(){
		return $this->categoryId;
	}
	public function getSiteType(){
		return $this->siteType;
	}
	public function getDiscountPriceMin(){
		return $this->discountPriceMin;
	}
	public function getDiscountPriceMax(){
		return $this->discountPriceMax;
	}
	public function getCommissionRateMin(){
		return $this->commissionRateMin;
	}
	public function getCommissionRateMax(){
		return $this->commissionRateMax;
	}
	public function getSort(){
		return $this->sort;
	}
	public function getSortType(){
		return $this->sortType;
	}
	public function getCurPage(){
		return $this->curPage;
	}
	public function getPageRows(){
		return $this->pageRows;
	}

	public function setTrackerU($trackerU){
		$this->trackerU = $trackerU;
		$this->apiParas["trackerU"] = $trackerU;
	}
	public function setWebsiteId($websiteId){
		$this->websiteId = $websiteId;
		$this->apiParas["websiteId"] = $websiteId;
	}
	public function setUid($uid){
		$this->uid = $uid;
		$this->apiParas["uid"] = $uid;
	}
	public function setProvinceId($provinceId){
		$this->provinceId = $provinceId;
		$this->apiParas["provinceId"] = $provinceId;
	}
	public function setKeyword($keyword){
		$this->keyword = $keyword;
		$this->apiParas["keyword"] = $keyword;
	}
	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
	}
	public function setSiteType($siteType){
		$this->siteType = $siteType;
		$this->apiParas["siteType"] = $siteType;
	}
	public function setDiscountPriceMin($discountPriceMin){
		$this->discountPriceMin = $discountPriceMin;
		$this->apiParas["discountPriceMin"] = $discountPriceMin;
	}
	public function setDiscountPriceMax($discountPriceMax){
		$this->discountPriceMax = $discountPriceMax;
		$this->apiParas["discountPriceMax"] = $discountPriceMax;
	}
	public function setCommissionRateMin($commissionRateMin){
		$this->commissionRateMin = $commissionRateMin;
		$this->apiParas["commissionRateMin"] = $commissionRateMin;
	}
	public function setCommissionRateMax($commissionRateMax){
		$this->commissionRateMax = $commissionRateMax;
		$this->apiParas["commissionRateMax"] = $commissionRateMax;
	}
	public function setSort($sort){
		$this->sort = $sort;
		$this->apiParas["sort"] = $sort;
	}
	public function setSortType($sortType){
		$this->sortType = $sortType;
		$this->apiParas["sortType"] = $sortType;
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
