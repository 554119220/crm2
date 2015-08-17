<?php

/**
 * 缺货通知信息
 * @author auto create
 */
class WlbWmsInventoryLackUpload
{
	
	/** 
	 * 创建时间
	 **/
	public $createTime;
	
	/** 
	 * 商品信息列表
	 **/
	public $itemList;
	
	/** 
	 * 订单编码
	 **/
	public $orderCode;
	
	/** 
	 * 外部业务编码;消息ID，用于去重
	 **/
	public $outBizCode;
	
	/** 
	 * 仓库编码
	 **/
	public $storeCode;
	
	/** 
	 * 仓储订单编码
	 **/
	public $storeOrderCode;
	
	/** 
	 * 仓库编码
	 **/
	public $stroreCode;	
}
?>