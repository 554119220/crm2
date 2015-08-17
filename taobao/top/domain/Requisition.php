<?php

/**
 * 合作申请
 * @author auto create
 */
class Requisition
{
	
	/** 
	 * 好评率
	 **/
	public $distAppraise;
	
	/** 
	 * 主营类目
	 **/
	public $distCategory;
	
	/** 
	 * 主营类目名称
	 **/
	public $distCategoryName;
	
	/** 
	 * 是否消保(0-不是、1-是)
	 **/
	public $distIsXiaobao;
	
	/** 
	 * 店铺星级
	 **/
	public $distLevel;
	
	/** 
	 * 分销申请加盟时，给供应商的留言
	 **/
	public $distMessage;
	
	/** 
	 * 开店时间
	 **/
	public $distOpenDate;
	
	/** 
	 * 店铺地址
	 **/
	public $distShopAddress;
	
	/** 
	 * 分销商id
	 **/
	public $distributorId;
	
	/** 
	 * 分销商nick
	 **/
	public $distributorNick;
	
	/** 
	 * 申请时间
	 **/
	public $gmtCreate;
	
	/** 
	 * 合作申请ID
	 **/
	public $requisitionId;
	
	/** 
	 * 申请状态（1-申请中、2-成功、3-被退回、4-已撤消、5-过期）
	 **/
	public $status;	
}
?>