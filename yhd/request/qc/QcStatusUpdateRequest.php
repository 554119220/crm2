<?php
/**
*资质审核状态更新
*/

class QcStatusUpdateRequest {

    private $isCompatible = 0;
    
	private $apiParas = array();
		
	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function getApiMethodName()
	{
		return "yhd.qc.status.update";
	}
	
	/**商家订单ID*/
	private  $customOrderId; 
	/**用户名称。用户注册帐号名称*/
	private  $userName; 
	/**审核状态1：审核申请已受理，请保持电话畅通，咨询请致电027-59397322、2：请根据认证邮件的内容，提交审核所需的相关资料、3：资料已提交、资质审核中，请耐心等候、4：资料有缺失，请根据反馈邮件提示尽快提交、注：状态1为派单成功后双方默认状态。状态2、4我们可以添加备注，认证员填写自己的直线电话所以去掉了电话号码。*/
	private  $status; 
	/**状态备注。最大1024个字符*/
	private  $statusRemark; 

	public function getCustomOrderId(){
		return $this->customOrderId;
	}
	public function getUserName(){
		return $this->userName;
	}
	public function getStatus(){
		return $this->status;
	}
	public function getStatusRemark(){
		return $this->statusRemark;
	}

	public function setCustomOrderId($customOrderId){
		$this->customOrderId = $customOrderId;
		$this->apiParas["customOrderId"] = $customOrderId;
	}
	public function setUserName($userName){
		$this->userName = $userName;
		$this->apiParas["userName"] = $userName;
	}
	public function setStatus($status){
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}
	public function setStatusRemark($statusRemark){
		$this->statusRemark = $statusRemark;
		$this->apiParas["statusRemark"] = $statusRemark;
	}

	public function getIsCompatible(){
		return $this->isCompatible;
	}
}
