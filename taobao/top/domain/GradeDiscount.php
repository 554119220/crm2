<?php

/**
 * 等级折扣数据结构
 * @author auto create
 */
class GradeDiscount
{
	
	/** 
	 * 等级ID或分销商ID
	 **/
	public $discountId;
	
	/** 
	 * 折扣类型：1-等级、2-分销商折扣
	 **/
	public $discountType;
	
	/** 
	 * 采购价格
	 **/
	public $price;
	
	/** 
	 * skuId
	 **/
	public $skuId;
	
	/** 
	 * 模式：1-代销、2-经销
	 **/
	public $tradeType;	
}
?>