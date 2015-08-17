<?php

/**
 * 商品优惠策略详情
 * @author auto create
 */
class Promotion
{
	
	/** 
	 * 减价件数，1只减一件，0表示多件
	 **/
	public $decreaseNum;
	
	/** 
	 * 优惠类型，PRICE表示按价格优惠，DISCOUNT表示按折扣优惠
	 **/
	public $discountType;
	
	/** 
	 * 优惠额度
	 **/
	public $discountValue;
	
	/** 
	 * 优惠结束日期
	 **/
	public $endDate;
	
	/** 
	 * 是否免邮（暂不可用，预留参数）
	 **/
	public $freePostage;
	
	/** 
	 * 预留字段
	 **/
	public $groupId;
	
	/** 
	 * 带指定渠道参数的宝贝详情URL
	 **/
	public $itemDetailUrl;
	
	/** 
	 * 商品数字ID
	 **/
	public $numIid;
	
	/** 
	 * 优惠描述
	 **/
	public $promotionDesc;
	
	/** 
	 * 优惠ID
	 **/
	public $promotionId;
	
	/** 
	 * 优惠标题，显示在宝贝详情页面的优惠图标的tip。
	 **/
	public $promotionTitle;
	
	/** 
	 * 优惠开始日期
	 **/
	public $startDate;
	
	/** 
	 * 优惠策略状态，ACTIVE表示有效，UNACTIVE表示无效
	 **/
	public $status;
	
	/** 
	 * 对应的人群标签ID
	 **/
	public $tagId;	
}
?>