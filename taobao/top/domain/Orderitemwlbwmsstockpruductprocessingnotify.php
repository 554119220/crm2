<?php

/**
 * 商品列表
 * @author auto create
 */
class Orderitemwlbwmsstockpruductprocessingnotify
{
	
	/** 
	 * 商品失效日期
	 **/
	public $dueDate;
	
	/** 
	 * 拓展属性
	 **/
	public $extendFields;
	
	/** 
	 * 库存类型 1 正品 101 残次 102 机损 103 箱损 201 冻结库存 301 在途库存
	 **/
	public $inventoryType;
	
	/** 
	 * 后端商品ID，特指原料
	 **/
	public $itemId;
	
	/** 
	 * ERP明细行号
	 **/
	public $orderItemId;
	
	/** 
	 * 计划数量
	 **/
	public $planQty;
	
	/** 
	 * 商品生产批号
	 **/
	public $produceCode;
	
	/** 
	 * 商品生产日期
	 **/
	public $produceDate;
	
	/** 
	 * 配比数量
	 **/
	public $ratioQty;
	
	/** 
	 * 备注
	 **/
	public $remark;	
}
?>