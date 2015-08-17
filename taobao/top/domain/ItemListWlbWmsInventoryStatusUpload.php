<?php

/**
 * 商品信息列表
 * @author auto create
 */
class ItemListWlbWmsInventoryStatusUpload
{
	
	/** 
	 * WMS批次号
	 **/
	public $batchCode;
	
	/** 
	 * 商品过期日期YYYY-MM-DD
	 **/
	public $dueDate;
	
	/** 
	 * 调整标示：0 增加 、1减少
	 **/
	public $intOutFlag;
	
	/** 
	 * 库存类型 1 正品，101 残次，102 机损，103 箱损，201 冻结库存，301 在途库存
	 **/
	public $inventoryType;
	
	/** 
	 * 后端商品ID
	 **/
	public $itemId;
	
	/** 
	 * 生产批号
	 **/
	public $produceCode;
	
	/** 
	 * 商品生产日期 YYYY-MM-DD
	 **/
	public $produceDate;
	
	/** 
	 * 数量
	 **/
	public $quantity;
	
	/** 
	 * 序列号
	 **/
	public $snCode;	
}
?>