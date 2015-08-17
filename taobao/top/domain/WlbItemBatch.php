<?php

/**
 * 批次库存查询结果记录
 * @author auto create
 */
class WlbItemBatch
{
	
	/** 
	 * 批次编号
	 **/
	public $batchCode;
	
	/** 
	 * 创建者
	 **/
	public $creator;
	
	/** 
	 * 残次数量
	 **/
	public $defectQuantity;
	
	/** 
	 * 到期时间
	 **/
	public $dueDate;
	
	/** 
	 * 创建时间
	 **/
	public $gmtCreate;
	
	/** 
	 * 最后修改时间
	 **/
	public $gmtModified;
	
	/** 
	 * 保质期
	 **/
	public $guaranteePeriod;
	
	/** 
	 * 天（单位）
	 **/
	public $guaranteeUnit;
	
	/** 
	 * 商品批次记录id
	 **/
	public $id;
	
	/** 
	 * 是否删除。0：正常 1：删除
	 **/
	public $isDeleted;
	
	/** 
	 * 商品id
	 **/
	public $itemId;
	
	/** 
	 * 最后修改者
	 **/
	public $lastModifier;
	
	/** 
	 * 产地
	 **/
	public $produceArea;
	
	/** 
	 * 生产编号
	 **/
	public $produceCode;
	
	/** 
	 * 生产日期
	 **/
	public $produceDate;
	
	/** 
	 * 商品数量
	 **/
	public $quantity;
	
	/** 
	 * 接受日期
	 **/
	public $receiveDate;
	
	/** 
	 * 描述
	 **/
	public $remarks;
	
	/** 
	 * 状态。item_batch_status_open:开放 item_batch_status_lock:冻结 item_batch_status_invalid:无效
	 **/
	public $status;
	
	/** 
	 * 存储类型
	 **/
	public $storeCode;
	
	/** 
	 * 用户id
	 **/
	public $userId;
	
	/** 
	 * 版本
	 **/
	public $version;	
}
?>