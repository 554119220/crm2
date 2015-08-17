<?php

/**
 * 授权关系
 * @author auto create
 */
class WlbAuthorization
{
	
	/** 
	 * 授权结束时间
	 **/
	public $authorizeEndTime;
	
	/** 
	 * 授权ID
	 **/
	public $authorizeId;
	
	/** 
	 * 授权开始时间
	 **/
	public $authorizeStartTime;
	
	/** 
	 * 代销人用户ID
	 **/
	public $consignUserId;
	
	/** 
	 * 授权商品ID
	 **/
	public $itemId;
	
	/** 
	 * 授权名称
	 **/
	public $name;
	
	/** 
	 * 货主用户ID
	 **/
	public $ownerUserId;
	
	/** 
	 * 授权数量
	 **/
	public $quantity;
	
	/** 
	 * 授权编码
	 **/
	public $ruleCode;
	
	/** 
	 * 状态：
VALID -- 1 有效
INVALIDATION -- 2 失效
	 **/
	public $status;	
}
?>