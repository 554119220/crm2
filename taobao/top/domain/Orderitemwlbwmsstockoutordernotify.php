<?php

/**
 * 订单商品信息
 * @author auto create
 */
class Orderitemwlbwmsstockoutordernotify
{
	
	/** 
	 * 批次号
	 **/
	public $batchCode;
	
	/** 
	 * 到货日期
	 **/
	public $dueDate;
	
	/** 
	 * 订单商品拓展属性数据
	 **/
	public $extendFields;
	
	/** 
	 * 库存类型
	 **/
	public $inventoryType;
	
	/** 
	 * ERP商品ID
	 **/
	public $itemId;
	
	/** 
	 * 商品数量
	 **/
	public $itemQuantity;
	
	/** 
	 * ERP主键ID
	 **/
	public $orderItemId;
	
	/** 
	 * 生产编码，同一商品可能因商家不同有不同编码
	 **/
	public $produceCode;
	
	/** 
	 * 生产日期
	 **/
	public $produceDate;	
}
?>