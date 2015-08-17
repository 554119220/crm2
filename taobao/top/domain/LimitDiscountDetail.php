<?php

/**
 * 限时打折详情
 * @author auto create
 */
class LimitDiscountDetail
{
	
	/** 
	 * 限时打折结束时间。
	 **/
	public $endTime;
	
	/** 
	 * 该商品限时折扣
	 **/
	public $itemDiscount;
	
	/** 
	 * 商品的id(数字类型)
	 **/
	public $itemId;
	
	/** 
	 * 限时打折名称
	 **/
	public $limitDiscountName;
	
	/** 
	 * 每人限购数量，1、2、5、10000(不限)。
	 **/
	public $limitNum;
	
	/** 
	 * 限时打折开始时间。
	 **/
	public $startTime;	
}
?>