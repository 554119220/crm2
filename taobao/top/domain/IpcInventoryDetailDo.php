<?php

/**
 * 库存明细
 * @author auto create
 */
class IpcInventoryDetailDo
{
	
	/** 
	 * 主订单号
	 **/
	public $bizOrderId;
	
	/** 
	 * 子订单号
	 **/
	public $bizSubOrderId;
	
	/** 
	 * 1拍减 2付减
	 **/
	public $flag;
	
	/** 
	 * 占用数量
	 **/
	public $occupyQuantity;
	
	/** 
	 * 货主昵称
	 **/
	public $ownerNick;
	
	/** 
	 * 预扣库存数
	 **/
	public $reserveQuantity;
	
	/** 
	 * 仓储商品id
	 **/
	public $scItemId;
	
	/** 
	 * 仓库编码
	 **/
	public $storeCode;	
}
?>