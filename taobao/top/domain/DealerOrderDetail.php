<?php

/**
 * 采购申请/经销采购单中的商品明细
 * @author auto create
 */
class DealerOrderDetail
{
	
	/** 
	 * 经销采购单明细id
	 **/
	public $dealerDetailId;
	
	/** 
	 * 经销采购单编号
	 **/
	public $dealerOrderId;
	
	/** 
	 * 最终价格(精确到2位小数;单位:元。如:200.07，表示:200元7分 )
	 **/
	public $finalPrice;
	
	/** 
	 * 该条明细是否已删除。true：已删除；false：未删除。
	 **/
	public $isDeleted;
	
	/** 
	 * 原始价格(精确到2位小数;单位:元。如:200.07，表示:200元7分 )
	 **/
	public $originalPrice;
	
	/** 
	 * 金额小计(采购数量乘以最终价格。精确到2位小数;单位:元。如:200.07，表示:200元7分 )
	 **/
	public $priceCount;
	
	/** 
	 * 产品id
	 **/
	public $productId;
	
	/** 
	 * 产品标题
	 **/
	public $productTitle;
	
	/** 
	 * 采购数量
	 **/
	public $quantity;
	
	/** 
	 * sku id
	 **/
	public $skuId;
	
	/** 
	 * 商家编码，是sku则为sku的商家编码，否则是产品的商家编码
	 **/
	public $skuNumber;
	
	/** 
	 * 产品规格
	 **/
	public $skuSpec;
	
	/** 
	 * 产品快照url
	 **/
	public $snapshotUrl;	
}
?>