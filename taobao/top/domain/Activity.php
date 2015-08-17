<?php

/**
 * 活动数据结构
 * @author auto create
 */
class Activity
{
	
	/** 
	 * 活动id
	 **/
	public $activityId;
	
	/** 
	 * 领用优惠券的链接
	 **/
	public $activityUrl;
	
	/** 
	 * 已经领取的优惠券的数量
	 **/
	public $appliedCount;
	
	/** 
	 * 活动对应的优惠券ID
	 **/
	public $couponId;
	
	/** 
	 * self代表自己创建，other他人创建
	 **/
	public $createUser;
	
	/** 
	 * 每个买家限领取优惠券的数量，1～5张
	 **/
	public $personLimitCount;
	
	/** 
	 * enabled代表有效，invalid代表失效。other代表空值
	 **/
	public $status;
	
	/** 
	 * 卖家设置优惠券领取的总领用量
	 **/
	public $totalCount;	
}
?>