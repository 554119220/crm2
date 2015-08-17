<?php
/**
*资质审核信息更新
*/

class QcInfoUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.qc.info.update";
	}
	
	/**商家订单ID*/
	private  $customOrderId; 
	/**用户名称。用户注册帐号名称*/
	private  $userName; 
	/**认证结果。0：未通过；1：通过*/
	private  $certified; 
	/**审核通过的申请类目*/
	private  $categorys; 
	/**品牌名称、授权级别列表，多个单位之间逗号分隔，每个单位的品牌和授权级别之间冒号分隔。*/
	private  $brands; 
	/**经营类型。1：专卖店；2：专营店；3：旗舰店；*/
	private  $shopCategory; 
	/**拒绝类型。1:基本资料填写错误、2:资料提供不齐全、3:基本资质不合格、99:其它*/
	private  $refuseType; 
	/**拒绝原因说明。最大是1024字符。*/
	private  $refuseDesc; 
	/**订单完成时间*/
	private  $finished; 
	/**企业状态*/
	private  $liceStateTitle; 
	/**公司名称*/
	private  $comName; 
	/**公司注册号*/
	private  $regNum; 
	/**企业注册地址*/
	private  $regAddress; 
	/**法定代表人*/
	private  $legalRep; 
	/**注册资本（万元）*/
	private  $regCapital; 
	/**企业类型*/
	private  $comType; 
	/**经营范围。最大2048个字符*/
	private  $businessScope; 
	/**成立日期*/
	private  $foundDate; 
	/**经营期限开始时间*/
	private  $liceStartDate; 
	/**经营期限结束时间*/
	private  $liceEndDate; 
	/**登记机关*/
	private  $issuingAuthority; 
	/**最近年检时间（年份）*/
	private  $lastReviewedDate; 
	/**企业工商资质是否通过。1：通过；0：未通过*/
	private  $comCertified; 
	/**工商资质备注*/
	private  $comRemark; 
	/**品牌相关数据列表。具体参数封装为json格式*/
	private  $brandList; 
	/**授权店长姓名。审核通过（certified=1）时，才可以更新此字段。*/
	private  $grantors; 

	public function getCustomOrderId(){
		return $this->customOrderId;
	}
	public function getUserName(){
		return $this->userName;
	}
	public function getCertified(){
		return $this->certified;
	}
	public function getCategorys(){
		return $this->categorys;
	}
	public function getBrands(){
		return $this->brands;
	}
	public function getShopCategory(){
		return $this->shopCategory;
	}
	public function getRefuseType(){
		return $this->refuseType;
	}
	public function getRefuseDesc(){
		return $this->refuseDesc;
	}
	public function getFinished(){
		return $this->finished;
	}
	public function getLiceStateTitle(){
		return $this->liceStateTitle;
	}
	public function getComName(){
		return $this->comName;
	}
	public function getRegNum(){
		return $this->regNum;
	}
	public function getRegAddress(){
		return $this->regAddress;
	}
	public function getLegalRep(){
		return $this->legalRep;
	}
	public function getRegCapital(){
		return $this->regCapital;
	}
	public function getComType(){
		return $this->comType;
	}
	public function getBusinessScope(){
		return $this->businessScope;
	}
	public function getFoundDate(){
		return $this->foundDate;
	}
	public function getLiceStartDate(){
		return $this->liceStartDate;
	}
	public function getLiceEndDate(){
		return $this->liceEndDate;
	}
	public function getIssuingAuthority(){
		return $this->issuingAuthority;
	}
	public function getLastReviewedDate(){
		return $this->lastReviewedDate;
	}
	public function getComCertified(){
		return $this->comCertified;
	}
	public function getComRemark(){
		return $this->comRemark;
	}
	public function getBrandList(){
		return $this->brandList;
	}
	public function getGrantors(){
		return $this->grantors;
	}

	public function setCustomOrderId($customOrderId){
		$this->customOrderId = $customOrderId;
		$this->apiParas["customOrderId"] = $customOrderId;
	}
	public function setUserName($userName){
		$this->userName = $userName;
		$this->apiParas["userName"] = $userName;
	}
	public function setCertified($certified){
		$this->certified = $certified;
		$this->apiParas["certified"] = $certified;
	}
	public function setCategorys($categorys){
		$this->categorys = $categorys;
		$this->apiParas["categorys"] = $categorys;
	}
	public function setBrands($brands){
		$this->brands = $brands;
		$this->apiParas["brands"] = $brands;
	}
	public function setShopCategory($shopCategory){
		$this->shopCategory = $shopCategory;
		$this->apiParas["shopCategory"] = $shopCategory;
	}
	public function setRefuseType($refuseType){
		$this->refuseType = $refuseType;
		$this->apiParas["refuseType"] = $refuseType;
	}
	public function setRefuseDesc($refuseDesc){
		$this->refuseDesc = $refuseDesc;
		$this->apiParas["refuseDesc"] = $refuseDesc;
	}
	public function setFinished($finished){
		$this->finished = $finished;
		$this->apiParas["finished"] = $finished;
	}
	public function setLiceStateTitle($liceStateTitle){
		$this->liceStateTitle = $liceStateTitle;
		$this->apiParas["liceStateTitle"] = $liceStateTitle;
	}
	public function setComName($comName){
		$this->comName = $comName;
		$this->apiParas["comName"] = $comName;
	}
	public function setRegNum($regNum){
		$this->regNum = $regNum;
		$this->apiParas["regNum"] = $regNum;
	}
	public function setRegAddress($regAddress){
		$this->regAddress = $regAddress;
		$this->apiParas["regAddress"] = $regAddress;
	}
	public function setLegalRep($legalRep){
		$this->legalRep = $legalRep;
		$this->apiParas["legalRep"] = $legalRep;
	}
	public function setRegCapital($regCapital){
		$this->regCapital = $regCapital;
		$this->apiParas["regCapital"] = $regCapital;
	}
	public function setComType($comType){
		$this->comType = $comType;
		$this->apiParas["comType"] = $comType;
	}
	public function setBusinessScope($businessScope){
		$this->businessScope = $businessScope;
		$this->apiParas["businessScope"] = $businessScope;
	}
	public function setFoundDate($foundDate){
		$this->foundDate = $foundDate;
		$this->apiParas["foundDate"] = $foundDate;
	}
	public function setLiceStartDate($liceStartDate){
		$this->liceStartDate = $liceStartDate;
		$this->apiParas["liceStartDate"] = $liceStartDate;
	}
	public function setLiceEndDate($liceEndDate){
		$this->liceEndDate = $liceEndDate;
		$this->apiParas["liceEndDate"] = $liceEndDate;
	}
	public function setIssuingAuthority($issuingAuthority){
		$this->issuingAuthority = $issuingAuthority;
		$this->apiParas["issuingAuthority"] = $issuingAuthority;
	}
	public function setLastReviewedDate($lastReviewedDate){
		$this->lastReviewedDate = $lastReviewedDate;
		$this->apiParas["lastReviewedDate"] = $lastReviewedDate;
	}
	public function setComCertified($comCertified){
		$this->comCertified = $comCertified;
		$this->apiParas["comCertified"] = $comCertified;
	}
	public function setComRemark($comRemark){
		$this->comRemark = $comRemark;
		$this->apiParas["comRemark"] = $comRemark;
	}
	public function setBrandList($brandList){
		$this->brandList = $brandList;
		$this->apiParas["brandList"] = $brandList;
	}
	public function setGrantors($grantors){
		$this->grantors = $grantors;
		$this->apiParas["grantors"] = $grantors;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
