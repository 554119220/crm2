<?php

/**
 * 后端商品映射关系对象
 * @author auto create
 */
class ScItemMap
{
	
	/** 
	 * map_type=1时，item_id为IC商品id

map_type=7时，item_id为分销商品id
	 **/
	public $itemId;
	
	/** 
	 * 1:前台和后台关系
7:分销和后台关系
	 **/
	public $mapType;
	
	/** 
	 * 后端商品ID
	 **/
	public $relItemId;
	
	/** 
	 * 后端商品所有者商家编码
	 **/
	public $relOuterCode;
	
	/** 
	 * 后端商品所有者id
	 **/
	public $relUserId;
	
	/** 
	 * 后端商品所有者名称
	 **/
	public $relUserNick;
	
	/** 
	 * 当宝贝下没SKU时该字段为空
	 **/
	public $skuId;
	
	/** 
	 * Ic商家id(分销商id)
	 **/
	public $userId;
	
	/** 
	 * Ic商家nick(分销商nick)
	 **/
	public $userNick;	
}
?>