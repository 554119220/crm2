<?php
/**
*批量查询网盟店铺信息
*/

class UnionStoresGetRequest {

    private $isCompatible = 1;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.union.stores.get";
	}
	
	/**联盟用户ID*/
	private  $trackerU; 
	/**网站ID*/
	private  $websiteId; 
	/**用户ID*/
	private  $uid; 
	/**商家ID*/
	private  $userId; 
	/**商家所在地*/
	private  $merchantProvinceName; 
	/**近期支出佣金量，开始值*/
	private  $recentlyCommissionExpensesStart; 
	/**近期支出佣金量，结束值*/
	private  $recentlyCommissionExpensesEnd; 
	/**指明返回哪些字段数据,用英文逗号分隔。根据请求数据字段，返回相应字段数据。默认不填为返回全部字段(可选项:user_id,seller_nick,shop_title,pic_url,shop_url,commission_rate,auction_count,merchant_province_name,item_score,item_status,item_differ,service_score,service_status,service_differ,delivery_score,delivery_status,delivery_differ,commision_level,merchant_categorys)*/
	private  $fields; 
	/**商家关键字*/
	private  $keyword; 
	/**商家经营类目ID*/
	private  $cid; 
	/**商家佣金比例查询开始值*/
	private  $startCommissionrate; 
	/**商家佣金比例查询结束值*/
	private  $endCommissionrate; 
	/**商家宝贝数量查询开始值*/
	private  $startAuctioncount; 
	/**商家宝贝数量查询结束值*/
	private  $endAuctioncount; 
	/**排序字段 目前支持的排序字段有("commision_rate","auction_count")两个属性，否则排序无效，其它内容时为错误信息*/
	private  $sortField; 
	/**排序类型，必须是desc和asc其中之一。不填时默认按照asc处理*/
	private  $sortType; 
	/**当前页码*/
	private  $pageNo; 
	/**每页最大条数(默认40，最大是40条，超过40时用最大值代替)*/
	private  $pageSize; 
	/**店铺的信用等级总共为20级 1-5:1heart-5heart;6-10:1diamond-5diamond;11-15:1crown-5crown;16-20:1goldencrown-5goldencrown,暂时保留*/
	private  $startCredit; 
	/**店铺的信用等级总共为20级 1-5:1heart-5heart;6-10:1diamond-5diamond;11-15:1crown-5crown;16-20:1goldencrown-5goldencrown，暂时保留*/
	private  $endCredit; 
	/**店铺累计推广量开始值,暂时保留*/
	private  $startTotalaction; 
	/**店铺累计推广数查询结束值，暂时保留*/
	private  $endTotalaction; 
	/**是否只显示商城店铺,暂时保留*/
	private  $onlyMall; 
	/**标识一个应用是否来在无线或者手机应用,如果是true则会使用其他规则加密点击串.如果不传值,则默认是false,暂时保留*/
	private  $isMobile; 

	public function getTrackerU(){
		return $this->trackerU;
	}
	public function getWebsiteId(){
		return $this->websiteId;
	}
	public function getUid(){
		return $this->uid;
	}
	public function getUserId(){
		return $this->userId;
	}
	public function getMerchantProvinceName(){
		return $this->merchantProvinceName;
	}
	public function getRecentlyCommissionExpensesStart(){
		return $this->recentlyCommissionExpensesStart;
	}
	public function getRecentlyCommissionExpensesEnd(){
		return $this->recentlyCommissionExpensesEnd;
	}
	public function getFields(){
		return $this->fields;
	}
	public function getKeyword(){
		return $this->keyword;
	}
	public function getCid(){
		return $this->cid;
	}
	public function getStartCommissionrate(){
		return $this->startCommissionrate;
	}
	public function getEndCommissionrate(){
		return $this->endCommissionrate;
	}
	public function getStartAuctioncount(){
		return $this->startAuctioncount;
	}
	public function getEndAuctioncount(){
		return $this->endAuctioncount;
	}
	public function getSortField(){
		return $this->sortField;
	}
	public function getSortType(){
		return $this->sortType;
	}
	public function getPageNo(){
		return $this->pageNo;
	}
	public function getPageSize(){
		return $this->pageSize;
	}
	public function getStartCredit(){
		return $this->startCredit;
	}
	public function getEndCredit(){
		return $this->endCredit;
	}
	public function getStartTotalaction(){
		return $this->startTotalaction;
	}
	public function getEndTotalaction(){
		return $this->endTotalaction;
	}
	public function getOnlyMall(){
		return $this->onlyMall;
	}
	public function getIsMobile(){
		return $this->isMobile;
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
	public function setUserId($userId){
		$this->userId = $userId;
		$this->apiParas["userId"] = $userId;
	}
	public function setMerchantProvinceName($merchantProvinceName){
		$this->merchantProvinceName = $merchantProvinceName;
		$this->apiParas["merchantProvinceName"] = $merchantProvinceName;
	}
	public function setRecentlyCommissionExpensesStart($recentlyCommissionExpensesStart){
		$this->recentlyCommissionExpensesStart = $recentlyCommissionExpensesStart;
		$this->apiParas["recentlyCommissionExpensesStart"] = $recentlyCommissionExpensesStart;
	}
	public function setRecentlyCommissionExpensesEnd($recentlyCommissionExpensesEnd){
		$this->recentlyCommissionExpensesEnd = $recentlyCommissionExpensesEnd;
		$this->apiParas["recentlyCommissionExpensesEnd"] = $recentlyCommissionExpensesEnd;
	}
	public function setFields($fields){
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}
	public function setKeyword($keyword){
		$this->keyword = $keyword;
		$this->apiParas["keyword"] = $keyword;
	}
	public function setCid($cid){
		$this->cid = $cid;
		$this->apiParas["cid"] = $cid;
	}
	public function setStartCommissionrate($startCommissionrate){
		$this->startCommissionrate = $startCommissionrate;
		$this->apiParas["startCommissionrate"] = $startCommissionrate;
	}
	public function setEndCommissionrate($endCommissionrate){
		$this->endCommissionrate = $endCommissionrate;
		$this->apiParas["endCommissionrate"] = $endCommissionrate;
	}
	public function setStartAuctioncount($startAuctioncount){
		$this->startAuctioncount = $startAuctioncount;
		$this->apiParas["startAuctioncount"] = $startAuctioncount;
	}
	public function setEndAuctioncount($endAuctioncount){
		$this->endAuctioncount = $endAuctioncount;
		$this->apiParas["endAuctioncount"] = $endAuctioncount;
	}
	public function setSortField($sortField){
		$this->sortField = $sortField;
		$this->apiParas["sortField"] = $sortField;
	}
	public function setSortType($sortType){
		$this->sortType = $sortType;
		$this->apiParas["sortType"] = $sortType;
	}
	public function setPageNo($pageNo){
		$this->pageNo = $pageNo;
		$this->apiParas["pageNo"] = $pageNo;
	}
	public function setPageSize($pageSize){
		$this->pageSize = $pageSize;
		$this->apiParas["pageSize"] = $pageSize;
	}
	public function setStartCredit($startCredit){
		$this->startCredit = $startCredit;
		$this->apiParas["startCredit"] = $startCredit;
	}
	public function setEndCredit($endCredit){
		$this->endCredit = $endCredit;
		$this->apiParas["endCredit"] = $endCredit;
	}
	public function setStartTotalaction($startTotalaction){
		$this->startTotalaction = $startTotalaction;
		$this->apiParas["startTotalaction"] = $startTotalaction;
	}
	public function setEndTotalaction($endTotalaction){
		$this->endTotalaction = $endTotalaction;
		$this->apiParas["endTotalaction"] = $endTotalaction;
	}
	public function setOnlyMall($onlyMall){
		$this->onlyMall = $onlyMall;
		$this->apiParas["onlyMall"] = $onlyMall;
	}
	public function setIsMobile($isMobile){
		$this->isMobile = $isMobile;
		$this->apiParas["isMobile"] = $isMobile;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
