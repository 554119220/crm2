<?php

/**
 * 分销API返回数据结构
 * @author auto create
 */
class Distributor
{
	
	/** 
	 * 分销商的支付宝帐户
	 **/
	public $alipayAccount;
	
	/** 
	 * 分销商的淘宝卖家评价
	 **/
	public $appraise;
	
	/** 
	 * 分销商店铺主营类目
	 **/
	public $categoryId;
	
	/** 
	 * 联系人
	 **/
	public $contactPerson;
	
	/** 
	 * 分销商创建时间 时间格式：yyyy-MM-dd HH:mm:ss
	 **/
	public $created;
	
	/** 
	 * 分销商Id
	 **/
	public $distributorId;
	
	/** 
	 * 分销商姓名
	 **/
	public $distributorName;
	
	/** 
	 * 分销商的email
	 **/
	public $email;
	
	/** 
	 * 分销商的真实姓名，认证姓名
	 **/
	public $fullName;
	
	/** 
	 * 店铺等级
	 **/
	public $level;
	
	/** 
	 * 分销商的手机号
	 **/
	public $mobilePhone;
	
	/** 
	 * 分销商的电话
	 **/
	public $phone;
	
	/** 
	 * 分销商的网店链接
	 **/
	public $shopWebLink;
	
	/** 
	 * 分销商卖家的开店时间
	 **/
	public $starts;
	
	/** 
	 * 分销商ID
	 **/
	public $userId;	
}
?>