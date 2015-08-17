<?php

/**
 * 商品库存对象
 * @author auto create
 */
class InventorySum
{
	
	/** 
	 * 库存类型：
1：正常 
2：损坏 
3：冻结 
10：质押 
11-20:商家自定义
	 **/
	public $inventoryType;
	
	/** 
	 * 库存类型名称
	 **/
	public $inventoryTypeName;
	
	/** 
	 * 总占用数量
	 **/
	public $occupyQuantity;
	
	/** 
	 * 总物理库存数量
	 **/
	public $quantity;
	
	/** 
	 * 总预扣数量
	 **/
	public $reserveQuantity;
	
	/** 
	 * 商品商家编码
	 **/
	public $scItemCode;
	
	/** 
	 * 商品后端ID，如果有传sc_item_code,参数可以为0
	 **/
	public $scItemId;
	
	/** 
	 * 商家仓库编码
	 **/
	public $storeCode;	
}
?>