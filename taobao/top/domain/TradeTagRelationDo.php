<?php

/**
 * 订单标签关联关系。tag_type的值有1,2,3,1为官方标，2为自定义标，3为主站只读标签。当标签为主站只读标签时，只有tag_name和tag_value信息，不含id和gmt_modified等信息
 * @author auto create
 */
class TradeTagRelationDo
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
	 * 该标签操作的历史记录
	 **/
	public $historyTradeTagRelations;
	
	/** 
	 * 订单标签记录id
	 **/
	public $id;
	
	/** 
	 * 标签名称
	 **/
	public $tagName;
	
	/** 
	 * 标签类型       1：官方标签      2：自定义标签     3：主站只读标签
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