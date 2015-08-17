<?php

/**
 * 历史标签记录
 * @author auto create
 */
class HistoryTradeRelationDo
{
	
	/** 
	 * 记录的创建时间
	 **/
	public $gmtCreated;
	
	/** 
	 * 记录的最新修改时间
	 **/
	public $gmtModified;
	
	/** 
	 * 订单标签记录id
	 **/
	public $id;
	
	/** 
	 * 标签名称
	 **/
	public $tagName;
	
	/** 
	 * 标签类型       1：官方标签      2：自定义标签
	 **/
	public $tagType;
	
	/** 
	 * 标签值，json格式
	 **/
	public $tagValue;
	
	/** 
	 * 订单id
	 **/
	public $tid;
	
	/** 
	 * 该标签在消费者端是否显示,0:不显示,1：显示
	 **/
	public $visible;	
}
?>