<?php

/**
 * 物流宝代销关系
 * @author auto create
 */
class WlbConsignMent
{
	
	/** 
	 * 代销关系id
	 **/
	public $id;
	
	/** 
	 * 代销商用户前台宝贝id
	 **/
	public $itemId;
	
	/** 
	 * 代销数量
	 **/
	public $number;
	
	/** 
	 * 供应商商品id
	 **/
	public $tgtItemId;
	
	/** 
	 * 供应商用户id
	 **/
	public $tgtUserId;
	
	/** 
	 * 代销商用户id
	 **/
	public $userId;	
}
?>