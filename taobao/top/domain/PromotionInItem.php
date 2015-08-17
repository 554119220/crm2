<?php

/**
 * 单品级优惠信息
 * @author auto create
 */
class PromotionInItem
{
	
	/** 
	 * 优惠描述
	 **/
	public $desc;
	
	/** 
	 * 优惠结束时间
	 **/
	public $endTime;
	
	/** 
	 * 优惠折后价格
	 **/
	public $itemPromoPrice;
	
	/** 
	 * 优惠展示名称
	 **/
	public $name;
	
	/** 
	 * 需要支付附加物，显示为+xxx。如：+20淘金币
	 **/
	public $otherNeed;
	
	/** 
	 * 赠送东西。如：送10商城积分
	 **/
	public $otherSend;
	
	/** 
	 * idValue的值
	 **/
	public $promotionId;
	
	/** 
	 * sku价格对应的id（保证二者顺序相同）
	 **/
	public $skuIdList;
	
	/** 
	 * sku价格列表
	 **/
	public $skuPriceList;
	
	/** 
	 * 优惠开始时间
	 **/
	public $startTime;	
}
?>