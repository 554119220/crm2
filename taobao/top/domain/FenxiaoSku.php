<?php

/**
 * 分销产品SKU
 * @author auto create
 */
class FenxiaoSku
{
	
	/** 
	 * 代销采购价，单位：元
	 **/
	public $costPrice;
	
	/** 
	 * 经销采购价
	 **/
	public $dealerCostPrice;
	
	/** 
	 * SkuID
	 **/
	public $id;
	
	/** 
	 * 名称
	 **/
	public $name;
	
	/** 
	 * 商家编码
	 **/
	public $outerId;
	
	/** 
	 * sku的销售属性组合字符串。格式:pid:vid;pid:vid,如:1627207:3232483;1630696:3284570,表示:机身颜色:军绿色;手机套餐:一电一充。
	 **/
	public $properties;
	
	/** 
	 * 库存
	 **/
	public $quantity;
	
	/** 
	 * 配额可用库存
	 **/
	public $quotaQuantity;
	
	/** 
	 * 预扣库存
	 **/
	public $reservedQuantity;
	
	/** 
	 * 关联的后端商品id
	 **/
	public $scitemId;
	
	/** 
	 * 市场价
	 **/
	public $standardPrice;	
}
?>